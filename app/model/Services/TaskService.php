<?php

namespace Model\Services;


use Nette,
	Nette\Database\Table\ActiveRow,
	Nette\Http\FileUpload,
	Nette\Utils\Validators,
	Nette\Utils\Strings;
use	Model\Repositories,
	Model\Domains\Task,
	Utilities;
use Taco\Nette\Http\FileRemove;


class TaskService extends Nette\Object
{

	/** @var taskRepository */
	private $taskRepository;
	
	/** @var tagRepository */
	private $tagRepository;
	
	/** @var accepted_taskRepository */
	private $accepted_taskRepository;
	
	/** @var Repositories\Attachment_typeRepository */
	private $attachmentTypeRepository;
	
	/** @var fileManager */
	private $fileManager;



	public function __construct(
			Repositories\TaskRepository $taskRepository,
			Repositories\TagRepository $tagRepository,
			Repositories\Accepted_taskRepository $accepted_taskRepository,
			Repositories\Attachment_typeRepository $attachmentTypeRepository,
			Utilities\FileManager $fileManager )
	{
		$this->taskRepository = $taskRepository;
		$this->tagRepository = $tagRepository;
		$this->accepted_taskRepository = $accepted_taskRepository;
		$this->attachmentTypeRepository = $attachmentTypeRepository;
		$this->fileManager = $fileManager;
	}



	/**
	 * Create new task from form array
	 * 
	 * @param int User Id of the author
	 * @param array form
	 * 
	 * @return ActiveRow
	 */
	public function createTask($user_id, array $formValues)
	{
		return $this->taskRepository->create(array(
				'owner' 		=> $user_id,
				'token'			=> $formValues['token'],
				'title' 		=> $formValues['title'],
				'description' 	=> $formValues['description'],
				'salary'		=> $formValues['salary'],
				'budget'		=> $formValues['budget'],
				'budget_type' 	=> $formValues['budget_type'],
				'workers'		=> $formValues['workers'],
				'status'		=> 0,
				'deadline'		=> $formValues['deadline'],
		));
	}


	/** 
 	 * Update task
 	 * 
 	 * @param Nette\Database\Table\ActiveRow 	$task
 	 * @param array 							$task update details
 	 * 
 	 * @return Nette\Database\Table\ActiveRow
 	 */
	public function update(Task $task, array $values)
	{
		return $this->taskRepository->update($task, $values);
	}


	/**
	 * Store tags to the corresponding task, remove unused in task
	 * 
	 * @param Task $task
	 * @param array $tags
	 */
	public function storeTags(Task $task, array $tags)
	{
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
	public function saveAttachment(Task $task, FileUpload $upload)
	{
		$contentType = $this->attachmentTypeRepository->findContentType($upload->contentType);
		$this->taskRepository->saveAttachment(
				$task,
				$this->fileManager->saveFile('tasks', $task->token, $upload),
				$contentType
				);
	}



	/**
	 * Remove task attachments
	 * 
	 * @param Task
	 * @param FileRemove
	 */
	public function removeAttachment(Task $task, FileRemove $file)
	{
		$this->taskRepository->removeAttachment(
				$task,
				$this->fileManager->removeFile('tasks', $task->token, $file)
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
		$acceptedTaskStatus = $this->accepted_taskRepository->getStatusById($userId, $task->id);

		if ($acceptedTaskStatus === FALSE) {
			return $this->accepted_taskRepository->insert($userId, $task->id, 1);
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
		if ( $this->accepted_taskRepository->getUsersNumberOfAssignedTasks($userId) > 0 ) {
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
		if ( $this->accepted_taskRepository->getUsersNumberOfAssignedTasks($userId) > 0 ) {
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
		return $this->taskRepository->getOwnerTasks($userId);
	}


	/**
	 * Obtain info whether the task has been accepted
	 * 
	 * @param  int  $token task token 
	 * @param  int  $userId
	 * 
	 * @return boolean 
	 */
	public function isAccepted($token, $userId)
	{
		$task = $this->getTaskByToken($token);
		return $this->accepted_taskRepository->isAccepted($task->id, $userId);
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
	public function generateTaskToken()
	{
		$alpha = str_shuffle("abcdefghijklmnopqrstvwuxyz0123456789");
		$length = 8;

		$row = true;
		while ( $row ) {
			for($i = 0, $token = '', $l = strlen($alpha) - 1; $i < $length; $i ++) {
				$token .= $alpha{mt_rand(0, $l)};
			}
			// Check if it does not already exist in DB
			$row = $this->isTokenInDatabase($token); //False if not found
		}

		return $token;
	}
}
