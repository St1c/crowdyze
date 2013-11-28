<?php
namespace Model\Services;

use Nette,
	Model\Repositories,
	Utilities,
	Nette\Database\Table\ActiveRow,
	Nette\Http\FileUpload,
	Nette\Utils\Validators,
	Nette\Utils\Strings;

class TaskService extends Nette\Object
{

	/** @var taskRepository */
	private $taskRepository;
	
	/** @var tagRepository */
	private $tagRepository;
	
	/** @var accepted_taskRepository */
	private $accepted_taskRepository;
	
	/** @var fileManager */
	private $fileManager;



	public function __construct(Repositories\TaskRepository $taskRepository,
			Repositories\TagRepository $tagRepository,
			Repositories\Accepted_taskRepository $accepted_taskRepository,
			Utilities\FileManager $fileManager )
	{
		$this->taskRepository = $taskRepository;
		$this->tagRepository = $tagRepository;
		$this->accepted_taskRepository = $accepted_taskRepository;
		$this->fileManager = $fileManager;
	}



	/**
	 * Create new task from form array
	 * 
	 * @param int User Id of the author
	 * @param array form
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
				'deadline'		=> $formValues['deadline'],
		));
	}


	/** 
 	 * Update task
 	 * 
 	 * @param Nette\Database\Table\ActiveRow 	$task
 	 * @param array 							$task update details
 	 * @return Nette\Database\Table\ActiveRow
 	 */
	public function update(ActiveRow $task, array $values)
	{
		return $this->taskRepository->update($task, $values);
	}


	/**
	 * Store tags to the corresponding task, remove unused in task
	 * 
	 * @param ActiveRow $task
	 * @param array $tags
	 */
	public function storeTags(ActiveRow $task, array $tags)
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
	 * @param ActiveRow
	 * @param int
	 */
	private function setTaskTag(ActiveRow $task, $tagId)
	{
		$task->related('task_has_tag')->insert(array(
			'tag_id' => $tagId
		));
	}


	/**
	 * Assign task to only certain departments
	 * 
	 * @param ActiveRow
	 * @param array departments
	 */
	public function setDepartment(ActiveRow $task, array $departments)
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
	 * @param ActiveRow
	 * @param string unique task ID
	 * @param FileUpload
	 */
	public function saveAttachment(ActiveRow $task, $uuid, FileUpload $upload)
	{
		$path = $this->fileManager->saveFile('tasks', $uuid, $upload);

		$task->related('task_attachment')->insert(array(
			'path' => $path
		));
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
	 * @return boolean TRUE|FALSE
	 */
	public function isTokenInDatabase($token)
	{
		return $this->taskRepository->getTaskByToken($token) ? TRUE : FALSE;
	}


	/**
	 * Generate unique task ID
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
