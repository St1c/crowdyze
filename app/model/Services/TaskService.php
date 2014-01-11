<?php

namespace Model\Services;


use Nette,
	Nette\Database\Table\ActiveRow,
	Nette\Http\FileUpload,
	Nette\Utils\Validators,
	Nette\Utils\Strings;
use	Model\Repositories,
	Model\Services,
	Model\Domains\Task,
	Utilities;
use Taco\Nette\Http\FileUploaded;


class TaskService extends Nette\Object
{

	/** @var taskRepository */
	private $taskRepository;
	
	/** @var tagRepository */
	private $tagRepository;
	
	/** @var Model\Repositories\Accepted_taskRepository */
	private $acceptedTaskRepository;
	
	/** @var Repositories\Attachment_typeRepository */
	private $attachmentTypeRepository;

	/** @var Repositories\Result_attachmentRepository */
	private $resultAttachmentRepository;

	/** @var fileManager */
	private $fileManager;

	/** @var Model\Services\PayService @inject */
	private $payService;


	/**
	 * DI
	 */
	public function __construct(
			Repositories\TaskRepository $taskRepository,
			Repositories\TagRepository $tagRepository,
			Repositories\Accepted_taskRepository $acceptedTaskRepository,
			Repositories\Attachment_typeRepository $attachmentTypeRepository,
			Services\FileManager $fileManager,
			Services\PayService $payService,
			Repositories\Result_attachmentRepository $resultAttachmentRepository
			)
	{
		$this->taskRepository = $taskRepository;
		$this->tagRepository = $tagRepository;
		$this->acceptedTaskRepository = $acceptedTaskRepository;
		$this->attachmentTypeRepository = $attachmentTypeRepository;
		$this->fileManager = $fileManager;
		$this->payService = $payService;
		$this->resultAttachmentRepository = $resultAttachmentRepository;
	}



	/**
	 * Create new task from form array
	 * 
	 * @param int User Id of the author
	 * @param array form
	 * 
	 * @return ActiveRow
	 */
	public function createTask(array $formValues)
	{
		$this->taskRepository->beginTransaction();
		$this->fileManager->beginTransaction();

		try {
			$task = $this->taskRepository->create(array(
					'owner' 		=> $formValues['owner'],
					//~ 'owner' => $user_id,
					//	@TODO Move to repository
					'token'			=> $this->generateTaskToken(),
					'title' 		=> $formValues['title'],
					'description' 	=> $formValues['description'],
					'salary'		=> $formValues['salary'],
					'budget_type' 	=> $formValues['budget_type'],
					'workers' 		=> $formValues['workers'],
					'deadline' 		=> $formValues['deadline'],
					'promotion' 	=> $formValues['promotion']
					));

			// Saving tags
			if (isset($formValues['tags'])) {
				$this->storeTags($task, $formValues['tags']);
			}

			// Saving departments 
			// if ( !empty($values['departments']) ) {
			// 	$this->taskService->setDepartments($task, $values['departments']);
			// }

			// Allocate money for the task from user's wallet
			$this->payService->createBudget($task, $formValues['owner']);
			
			// Saving attachments
			foreach ($formValues['attachments'] as $file) {
				if ($file instanceof FileUploaded) {
					if ($file->isRemove()) {
						$this->removeAttachment($task, $file);
					}
					else {
						$this->saveAttachment($task, $file);
					}
				}
				else {
					throw new \LogicException('Invalid type of attachment.');
				}
			}

			$this->taskRepository->commitTransaction();
			$this->fileManager->commitTransaction();
		}
		catch (\Exception $e) {
			$this->taskRepository->rollbackTransaction();
			$this->fileManager->rollbackTransaction();
			throw $e;
		}
		
		return $task;
	}



	public function deleteTask(Task $task)
	{
		$this->taskRepository->delete($task);
	}



	/** 
 	 * Update task
 	 * 
 	 * @param Nette\Database\Table\ActiveRow $task
 	 * @param array $task update details
 	 * 
 	 * @return Nette\Database\Table\ActiveRow
 	 */
	public function update(Task $task, array $values)
	{
		$this->taskRepository->beginTransaction();
		$this->fileManager->beginTransaction();

		try {
			$task = $this->taskRepository->update($task, self::array_filter_key($values, [
					'title',
					'description',
					'salary',
					'budget_type',
					'workers',
					'deadline',
					]));

			// Saving tags
			if (isset($values['tags'])) {
				$res = $this->storeTags($task, $values['tags']);
			}

			// Saving departments 
			// if ( !empty($values['departments']) ) {
			// 	$this->taskService->setDepartments($task, $values['departments']);
			// }

			// Allocate money for the task from user's wallet
			$this->payService->updateBudget($task, $task->owner);

			// Saving attachments
			foreach ($values['attachments'] as $file) {
				if ($file instanceof FileUploaded) {
					if ($file->isRemove()) {
						$this->removeAttachment($task, $file);
					}
					elseif (! $file->isCommited()) {
						$this->saveAttachment($task, $file);
					}
				}
				else {
					throw new \LogicException('Invalid type of attachment.');
				}
			}

			$this->fileManager->commitTransaction();
			$this->taskRepository->commitTransaction();
		}
		catch (\Exception $e) {
			$this->taskRepository->rollbackTransaction();
			$this->fileManager->rollbackTransaction();
			throw $e;
		}

		return $task;
	}



	/**
	 * Store tags to the corresponding task, remove unused in task
	 * 
	 * @param Task $task
	 * @param array $tags
	 */
	public function storeTags(Task $task, array $tags)
	{
		//	Old tags remove
		$task->related('task_has_tag')->delete();

		foreach (array_unique($tags) as $tag) {
			$tagInDb = $this->tagRepository->get($tag); 
			if ( empty($tagInDb) ) {
				$tagInDb = $this->tagRepository->create($tag);
			}
			$this->setTaskTag($task, $tagInDb->id);
		}
	}



	/**
	 * Connect tag to the task id
	 * 
	 * @param Task
	 * @param int
	 */
	private function setTaskTag(Task $task, $tagId)
	{
		$task->related('task_has_tag')->insert(array(
			'tag_id' => $tagId
		));
	}



	/**
	 * Assign task to only certain departments
	 * 
	 * @param Task
	 * @param array departments
	 */
	public function setDepartment(Task $task, array $departments)
	{
		foreach ($departments as $department) {
			$task->related('task_department')->insert(array(
				'department_id' => $department
			));	
		}
	}



	/**
	 * Save task attachments
	 * 
	 * @param Task
	 * @param FileUpload
	 */
	public function saveAttachment(Task $task, FileUploaded $file)
	{
		$contentType = $this->attachmentTypeRepository->findContentType($file->contentType);
		$this->taskRepository->saveAttachment(
				$task,
				$this->fileManager->saveFile('tasks', $task->token, $file),
				$contentType
				);
	}



	/**
	 * Remove task attachments
	 * 
	 * @param Task
	 * @param FileRemove
	 */
	public function removeAttachment(Task $task, FileUploaded $file)
	{
		$this->taskRepository->removeAttachment(
				$task,
				$this->fileManager->removeFile('tasks', $task->token, $file)
				);
	}



	/**
	 * Save task attachments
	 * 
	 * @param Task
	 * @param FileUpload
	 */
	public function saveResultAttachment($result, $task, FileUploaded $file)
	{
		$contentType = $this->attachmentTypeRepository->findContentType($file->contentType);
		$this->resultAttachmentRepository->saveAttachment(
				$result,
				$this->fileManager->saveFile('tasks', $task->token . '/results/' . $result->id, $file),
				$contentType
				);
	}



	/**
	 * Remove task attachments
	 * 
	 * @param Task
	 * @param FileRemove
	 */
	public function removeResultAttachment($result, $task, FileUploaded $file)
	{
		$this->resultAttachmentRepository->removeAttachment(
				$result,
				$this->fileManager->removeFile('tasks', $task->token . '/results/' . $result->id, $file)
				);
	}




	/**
	 * Accept Task
	 * 
	 * @param int User ID
	 * @param int Task ID
	 */
	public function acceptTask($userId, $token)
	{
		Validators::assert($userId, 'int');
		Validators::assert($token, 'string');

		$task = $this->getTaskByToken($token);
		$acceptedTaskStatus = $this->acceptedTaskRepository->getStatusById($userId, $task->id);

		if ($acceptedTaskStatus === FALSE) {
			return $this->acceptedTaskRepository->insert($userId, $task->id, 1); // what is it 1?
		}
		else {
			throw new \Exception("tasks.errors.already_accepted", 1);
		}
	}



	/**
	 * Get single task
	 * 
	 * @param int task ID
	 * 
	 * @return ActiveRow
	 */
	public function getTask($id)
	{
		Validators::assert($id, 'int');
		return $this->taskRepository->getTask($id);
	}



	/**
	 * Get task by token
	 * 
	 * @param  string $token
	 * 
	 * @return ActiveRow
	 */
	public function getTaskByToken($token)
	{
		Validators::assert($token, 'string');
		return $this->taskRepository->getTaskByToken($token);
	}



	/**
	 * Get all tasks which are not assigned to current user
	 * 
	 * @param  int $limit  Paginator Limit
	 * @param  int $offset Paginator offset
	 * @param  int $userId Users ID
	 * 
	 * @return Table\Selection         Filtered results
	 */
	public function getTasks($limit, $offset, $userId)
	{
		if ( $this->acceptedTaskRepository->getUsersNumberOfAssignedTasks($userId) > 0 ) {
			return $this->taskRepository->getTasksFilterByUserAccepted($limit, $offset, $userId);
		}
		else {
			return $this->getAllTasks($limit, $offset);
		}
	}



	/** 
 	 * Get tasks with paginator info
 	 * 
 	 * @param int $limit Paginator limit for one page
 	 * @param int $offset Paginator offset for current page in paginator
 	 * 
 	 * @return Nette\Database\Table\ActiveRow
 	 */
	public function getAllTasks($limit, $offset)
	{
		return $this->taskRepository->getTasks($limit, $offset);
	}

	

	/**
	 * Get promoted tasks which are not assigned to current user
	 * 
	 * @param  int $limit  Paginator Limit
	 * @param  int $offset Paginator offset
	 * @param  int $userId Users ID
	 * 
	 * @return Table\Selection         Filtered results
	 */
	public function getPromotedTasks($limit, $offset, $userId)
	{
		if ( $this->acceptedTaskRepository->getUsersNumberOfAssignedTasks($userId) > 0 ) {
			return $this->taskRepository->getPromotedTasksFilterByUserAccepted($limit, $offset, $userId);
		}
		else {
			return $this->getAllPromotedTasks($limit, $offset);
		}
	}



	/** 
 	 * Get promoted tasks with paginator info
 	 * 
 	 * @param int $limit Paginator limit for one page
 	 * @param int $offset Paginator offset for current page in paginator
 	 * 
 	 * @return Nette\Database\Table\ActiveRow
 	 */
	public function getAllPromotedTasks($limit, $offset)
	{
		return $this->taskRepository->getPromotedTasks($limit, $offset);
	}



	/**
	 * Get all tasks with certain tag which are not assigned to current user
	 * 
	 * @param  int $limit  Paginator Limit
	 * @param  int $offset Paginator offset
	 * @param  int $userId Users ID
	 * 
	 * @return Table\Selection         Filtered results
	 */
	public function getTaggedTasks($tag, $limit, $offset, $userId)
	{
		if ( $this->acceptedTaskRepository->getUsersNumberOfAssignedTasks($userId) > 0 ) {
			return $this->taskRepository->getTaggedTasksFilterByUserAccepted($tag, $limit, $offset, $userId);
		} else {
			return $this->getAllTaggedTasks($tag, $limit, $offset);
		}
	}



	/**
	 * Get tasks with certain tag
	 * 
	 * @param int Tag ID
	 * 
	 * @return Table\Selection
	 */
	public function getAllTaggedTasks($tag, $limit, $offset)
	{
		return $this->taskRepository->getTaggedTasks($tag, $limit, $offset);
	}



	/**
	 * Get promoted tasks with certain tag which are not assigned to current user
	 * 
	 * @param  int $limit  Paginator Limit
	 * @param  int $offset Paginator offset
	 * @param  int $userId Users ID
	 * 
	 * @return Table\Selection         Filtered results
	 */
	public function getPromotedTaggedTasks($tag, $limit, $offset, $userId)
	{
		if ( $this->acceptedTaskRepository->getUsersNumberOfAssignedTasks($userId) > 0 ) {
			return $this->taskRepository->getPromotedTaggedTasksFilterByUserAccepted($tag, $limit, $offset, $userId);
		} else {
			return $this->getAllPromotedTaggedTasks($tag, $limit, $offset);
		}
	}



	/**
	 * Get promoted tasks with certain tag
	 * 
	 * @param int Tag ID
	 * 
	 * @return Table\Selection
	 */
	public function getAllPromotedTaggedTasks($tag, $limit, $offset)
	{
		return $this->taskRepository->getPromotedTaggedTasks($tag, $limit, $offset);
	}


	/** 
 	 * Get number of tasks
 	 * 
 	 * @return int
 	 */
	public function getCount()
	{
		return $this->taskRepository->getCount();
	}



	/**
	 * Get number of tasks with specified tag
	 */
	public function getTagsTasksCount($tag)
	{
		return $this->taskRepository->getTagsTasksCount($tag);
	}



	/**
	 * Get tasks where current user is owner
	 * 
	 * @param  int $userId 
	 * 
	 * @return Table\Selection
	 */
	public function getOwnerTasks($userId)
	{
		Validators::assert($userId, 'int');
		return $this->taskRepository->getOwnerTasks($userId);
	}



	/**
	 * Obtain info whether the task has been accepted
	 * 
	 * @param string $token task token 
	 * @param int $userId
	 * 
	 * @return boolean 
	 */
	public function isAccepted($token, $userId)
	{
		Validators::assert($userId, 'int');
		Validators::assert($token, 'string');
		$task = $this->getTaskByToken($token);
		return $this->acceptedTaskRepository->isAccepted($task->id, $userId);
	}



	/**
	 * Check existance of the token (taks) in DB
	 * 
	 * @param  string  $token
	 * 
	 * @return boolean TRUE|FALSE
	 */
	public function isTokenInDatabase($token)
	{
		return $this->taskRepository->getTaskByToken($token) ? TRUE : FALSE;
	}



	/**
	 * Generate unique task ID
	 * 
	 * @return string 36^8 =  ~ 2.8 * 10^12 variations
	 */
	private function generateTaskToken()
	{
		$alpha = str_shuffle("abcdefghijklmnopqrstvwuxyz0123456789");
		$length = 8;
		$row = True;
		while ($row) {
			for($i = 0, $token = '', $l = strlen($alpha) - 1; $i < $length; $i ++) {
				$token .= $alpha{mt_rand(0, $l)};
			}

			// Check if it does not already exist in DB
			$row = $this->isTokenInDatabase($token); //False if not found
		}

		return $token;
	}



	/**
	 * Record new result and change status of accepted task to '2=pending'
	 * 
	 * @param int 	$userId
	 * @param int 	$taskId
	 * @param array $value Form Values
	 */
	public function createResult($userId, $taskId, array $values)
	{
		Validators::assert($userId, 'int');
		Validators::assert($taskId, 'int');
		Validators::assert($values['result'], 'string');

		$this->taskRepository->beginTransaction();
		$this->fileManager->beginTransaction();

		try {
			$repo = $this->acceptedTaskRepository;
			$acceptedTask = $this->acceptedTaskRepository->update($taskId, $userId, array(
				'result' => $values['result'],
				'status' => $repo::STATUS_PENDING
			));

			// Saving attachments
			foreach ($values['attachments'] as $file) {
				if ($file instanceof FileUploaded) {
					if ($file->isRemove()) {
						$this->removeResultAttachment($acceptedTask, $acceptedTask->task, $file);
					}
					else {
						$this->saveResultAttachment($acceptedTask, $acceptedTask->task, $file);
					}
				}
				else {
					throw new \LogicException('Invalid type of attachment.');
				}
			}
			
			$this->taskRepository->commitTransaction();
			$this->fileManager->commitTransaction();
		}
		catch (\Exception $e) {
			$this->taskRepository->rollbackTransaction();
			$this->fileManager->rollbackTransaction();
			throw $e;
		}
	}



	/**
	 * Get all pending results for given task
	 * 
	 * @param  int $taskId
	 * 
	 * @return Table\Selection
	 */
	public function getPendingResults($taskId)
	{
		Validators::assert($taskId, 'int');
		return $this->acceptedTaskRepository->getPending($taskId);
	}


	/**
	 * Get all results corresponding to thsi task
	 * 
	 * @param  Model\Domains\Task $task
	 * 
	 * @return Table\Selection      
	 */
	public function getResults($task)
	{
		return $task->related('accepted')->fetchAll();
	}



	/**
	 * Accept result
	 *
	 * @param  int $taskId
	 * @param  int $userId
	 */
	public function doAcceptResult($task, $userId)
	{
		Validators::assert($task->id, 'int');
		Validators::assert($userId, 'int');

		$this->taskRepository->beginTransaction();
		try {
			$this->payService->doPayResult($task, (int) $userId);
			$repo = $this->acceptedTaskRepository;
			$this->acceptedTaskRepository->updateStatus($task->id, $userId, $repo::STATUS_SATISFIED);
			$this->taskRepository->commitTransaction();
		}
		catch (\Exception $e) {
			$this->taskRepository->rollbackTransaction();
			throw $e;
		}
	}



	/**
	 * Reject result
	 * 
	 * @param  int $taskId
	 * @param  int $userId
	 */
	public function doRejectResult($taskId, $userId)
	{
		Validators::assert($taskId, 'int');
		Validators::assert($userId, 'int');
		$repo = $this->acceptedTaskRepository;
		$this->acceptedTaskRepository->updateStatus($taskId, $userId, $repo::STATUS_UNSATISFIED);
	}



	/**
	 * Get number of accepted/satisfied/pending
	 * 
	 * @param  Task   $task 
	 * 
	 * @return int    
	 */
	public function getFinishedCount(Task $task)
	{
		$repo = $this->acceptedTaskRepository;
		return $task->related('accepted')->where("status <> ?", $repo::STATUS_UNSATISFIED)->count();
	}



	/**
	 * In array leave only keys in $mask
	 * 
	 * @return array
	 */
	private static function array_filter_key(array $source, array $mask)
	{
		foreach ($source as $key => $_) {
			if (!in_array($key, $mask)) {
				unset($source[$key]);
			}
		}

		return $source;
	}

}
