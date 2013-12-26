<?php
namespace Model\Repositories;


use Nette\Database\Table\ActiveRow,
	Nette\Utils\Validators;
use Model\Domains\Task;


class TaskRepository extends BaseRepository
{

	/**
	 * Integrity constraint violation: 1062 Duplicate entry...
	 */
	const ERROR_DUPLICATE_ENTRY = 23000;


 	/** 
 	 * Create new task
 	 * 
 	 * @param array $task details
 	 * 
 	 * @return Nette\Database\Table\ActiveRow
 	 */
	public function create(array $task)
	{
		return self::createTask($this->getTable()->insert($task));
	}



	/** 
 	 * Update task
 	 * 
 	 * @param Task $task
 	 * @param array $task update details
 	 * 
 	 * @return Nette\Database\Table\ActiveRow
 	 */
	public function update(Task $task, array $values)
	{
		return $task->activeRow->update($values);
	}



	/**
	 * @param Task
	 * @param string $path
	 * @param int $contentType
	 */
	public function saveAttachment(Task $task, $path, $contentType)
	{
		//~ Validators::assert($contentType, 'int');
		try {
			$task->related('task_attachment')->insert(array(
					'path' => $path,
					'type_id' => $contentType,
					));
		}
		catch (\PDOException $e) {
			if (! self::ERROR_DUPLICATE_ENTRY == $e->getCode()) {
				throw $e;
			}

			$task->related('task_attachment')->select('`task_attachment`.id')
					->where('`task_attachment`.path', $path)
					->update(array(
							'type_id' => $contentType,
							));
		}
	}



	/**
	 * @param Task
	 * @param string $path
	 * @param int $contentType
	 */
	public function removeAttachment(Task $task, $path)
	{
		$task->related('task_attachment')->where('path', $path)->delete();
	}



	/**
	 * Get single task
	 * 
	 * @param int $id Task ID
	 * 
	 * @return ActiveRow
	 */
	public function getTask($id)
	{
		return $this->getTable()->where('id', $id)->fetch();
	}


	/**
	 * Get single task by token
	 * 
	 * @param  string $token
	 * 
	 * @return ActiveRow
	 */
	public function getTaskByToken($token)
	{
		if ($row = $this->getTable()->where('token', $token)->fetch()) {
			return self::createTask($row);
		}
	}


	/** 
 	 * Get all tasks
 	 * 
 	 * @param int limit for one page
 	 * @param int offset for current page in paginator
 	 * 
 	 * @return Nette\Database\Table\Selection
 	 */
	public function getTasks($limit, $offset)
	{
		return $this->getTable()
			->select('task.*, sum(:accepted_task.status <> (4) AND IFNULL(:accepted_task.status,0)) AS finished')
			->group('task.id')
			->having('finished < task.workers')
			->limit($limit, $offset)
			->order('created DESC');
	}


	/**
	 * Get tasks not assigned to current user
	 * 
	 * @param  int $limit  Paginator limit
	 * @param  int $offset Paginator offset
	 * @param  int $userId User's ID
	 * 
	 * @return Table\Selection         Filtered Results
	 */
	public function getTasksFilterByUserAccepted($limit, $offset, $userId)
	{
		// Subquery in where(NOT IN (?)) must not be NULL !
		return $this->getTable()
			->select('task.*, sum(:accepted_task.status <> (4) AND IFNULL(:accepted_task.status,0)) AS finished')
			->where('task.id NOT IN (?)', $this->table('accepted_task')->select('task_id')->where('user_id', $userId) )
			->group('task.id')
			->having('finished < task.workers')
			->limit($limit, $offset)
			->order('created DESC');
	}


	/** 
 	 * Get task's tags
 	 * 
 	 * @param int task id
 	 * 
 	 * @return Nette\Database\Table\ActiveRow
 	 */
	public function getTaskTags($taskId)
	{
		return $this->getTable()
			->select(':task_has_tag.tag.tag')
			->where('task.id', $taskId);
	}
	

	/**
	 * Get Tasks with tag ID
	 * 
	 * @param  string $tag    Tag
	 * @param  int $limit  Paginator limit
	 * @param  int $offset Paginator offset
	 * 
	 * @return Table\Selection         Filtered Results
	 */
	public function getTaggedTasks($tag, $limit, $offset)
	{
		return $this->getTable()
			->select('task.*, sum(:accepted_task.status <> (4) AND IFNULL(:accepted_task.status,0)) AS finished')
			->where(':task_has_tag.tag.tag', $tag)
			->group('task.id')
			->having('finished < task.workers')
			->limit($limit, $offset)
			->order('created DESC');
	}


	/**
	 * Get tasks with tag not assigned to current user
	 * 
	 * @param  string $tag Tag
	 * @param  int $limit  Paginator limit
	 * @param  int $offset Paginator offset
	 * @param  int $userId User's ID
	 * 
	 * @return Table\Selection         Filtered Results
	 */
	public function getTaggedTasksFilterByUserAccepted($tag, $limit, $offset, $userId)
	{
		// Subquery in where(NOT IN (?)) must not be NULL !
		return $this->getTable()
			->select('task.*, sum(:accepted_task.status <> (4) AND IFNULL(:accepted_task.status,0)) AS finished')
			->where(':task_has_tag.tag.tag', $tag)
			->where('task.id NOT IN (?)', $this->table('accepted_task')->select('task_id')->where('user_id', $userId) )
			->group('task.id')
			->having('finished < task.workers')
			->limit($limit, $offset)
			->order('created DESC');
	}


	/**
	 * Get tasks where current user is owner
	 * @param  int $userId 
	 * 
	 * @return Table\Selection
	 */
	public function getOwnerTasks($userId)
	{
		return $this->getTable()->where('owner', $userId);
	}


	/** 
 	 * Get number of all all tasks
 	 * 
 	 * @return int
 	 */
	public function getCount()
	{
		return $this->getTable()->select('task.*')->count('*');
	}


	/**
	 * Get number of tasks with specified tag
	 * 
	 * @param string $tag Tag name
	 * 
	 * @return ActiveRow Number of tasks with certain tag
	 */
	public function getTagsTasksCount($tag)
	{
		return $this->getTable()
			->select('task.*')
			->where(':task_has_tag.tag.tag', $tag)
			->count();
	}



	/**
	 * @param Nette\Database\Table\ActiveRow $data
	 */
	private static function createTask(ActiveRow $data)
	{
		//~ dump($data);
		$task = new Task($data/*, $data->id*/);
		//~ $task->title = $data->title;
		//~ $task->description = $data->description;
		//~ $task->salary = $data->salary;
		//~ $task->budgetType = $data->budget_type;
		//~ $task->workers = $data->workers;
		//~ $task->deadline = $data->deadline;
		//~ $task->token = $data->token;
		//~ $task->owner = $data->owner;
		//~ dump($task);
		//~ die('======');
		return $task;
	}

}
