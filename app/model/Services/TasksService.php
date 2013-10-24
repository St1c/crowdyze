<?php
namespace Model\Services;

use Nette,
	Model\Repositories,
	Utilities,
	Nette\Database\Table\ActiveRow,
	Nette\Http\FileUpload,
	Nette\Utils\Strings;

class TasksService extends Nette\Object
{

	/** @var tasksRepository */
	private $tasksRepository;
	/** @var tasks_tagsRepository */
	private $tasks_tagsRepository;
	/** @var accepted_tasksRepository */
	private $accepted_tasksRepository;
	/** @var fileManager */
	private $fileManager;


	public function __construct(Repositories\TasksRepository $tasksRepository,
								Repositories\Tasks_tagsRepository $tasks_tagsRepository,
								Repositories\Accepted_tasksRepository $accepted_tasksRepository,
								Utilities\FileManager $fileManager )
	{
		$this->tasksRepository 			= $tasksRepository;
		$this->tasks_tagsRepository 	= $tasks_tagsRepository;
		$this->accepted_tasksRepository = $accepted_tasksRepository;
		$this->fileManager 				= $fileManager;
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
		return $this->tasksRepository->create(array(
				'users_id' 		=> $user_id,
				'title' 		=> $formValues['title'],
				'description' 	=> $formValues['description'],
				'budget'		=> $formValues['budget'],
				'budget_type' 	=> $formValues['budget_type'],
				'workers'		=> $formValues['workers'],
				'deadline'		=> $formValues['deadline'],
		));
	}

	/**
	 * Add tags to the corresponding task
	 * 
	 * @param ActiveRow
	 * @param array
	 */
	public function addTags(ActiveRow $task, $tags)
	{

		foreach (Strings::split($tags, '~,\s*~') as $tag) {
				
			$tagInDb = $this->tasks_tagsRepository->get($tag); 

			if ( empty($tagInDb) ) {
				$tagInDb = $this->tasks_tagsRepository->create($tag);
			}

			$this->setTaskTags($task, $tagInDb->id);
		}
	}

	/**
	 * Connect tag to the task id
	 * 
	 * @param ActiveRow
	 * @param int
	 */
	private function setTaskTags(ActiveRow $task, $tagId)
	{
		$task->related('tasks_has_tags')->insert(array(
			'tasks_tags_id' => $tagId
		));
	}

	/**
	 * Assign task to only certain departments
	 * 
	 * @param ActiveRow
	 * @param array departments
	 */
	public function setDepartments(ActiveRow $task, $departments)
	{
		foreach ($departments as $department) {
			$task->related('tasks_departments')->insert(array(
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

		$task->related('tasks_attachments')->insert(array(
			'path' => $path
		));
	}

	/**
	 * Get single task
	 * 
	 * @param int task ID
	 * @return ActiveRow
	 */
	public function getTask($id)
	{
		return $this->tasksRepository->getTask($id);
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
		if ( $this->accepted_tasksRepository->getUsersNumberOfAssignedTasks($userId) > 0 ) {
			return $this->tasksRepository->getTasksFilterByUserAccepted($limit, $offset, $userId);
		} else {
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
		return $this->tasksRepository->getTasks($limit, $offset);
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
		if ( $this->accepted_tasksRepository->getUsersNumberOfAssignedTasks($userId) > 0 ) {
			return $this->tasksRepository->getTaggedTasksFilterByUserAccepted($tag, $limit, $offset, $userId);
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
		return $this->tasksRepository->getTaggedTasks($tag, $limit, $offset);
	}

	/** 
 	 * Get number of tasks
 	 * 
 	 * @return int
 	 */
	public function getCount()
	{
		return $this->tasksRepository->getCount();
	}

	/**
	 * Get number of tasks with specified tag
	 */
	public function getTagsTasksCount($tag)
	{
		return $this->tasksRepository->getTagsTasksCount($tag);
	}


	/**
	 * Accept Task
	 * 
	 * @param int User ID
	 * @param int Task ID
	 */
	public function acceptTask($userId, $taskId)
	{
		$acceptedTaskStatus = $this->accepted_tasksRepository->getStatusById($userId, $taskId);

		if ($acceptedTaskStatus === FALSE) {
			return $this->accepted_tasksRepository->insert($userId, $taskId, 1);
		} else {
			throw new \Exception("tasks.errors.already_accepted", 1);
		}
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
		return $this->tasksRepository->update($task, $values);
	}
}