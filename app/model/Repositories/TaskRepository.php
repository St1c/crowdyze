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
	public function create(array $data)
	{
		if (array_key_exists('promotion', $data) && empty($data['promotion'])) {
			$data['promotion'] = Null;
		}
		
		$data['token'] = $this->generateToken();
		return Task::createFromActiveRow($this->getTable()->insert($data)/*, $data->id*/);
	}



	/** 
 	 * Update task
 	 * 
 	 * @param Task $task
 	 * @param array $task update details
 	 * 
 	 * @return Task
 	 */
	public function update(Task $task, array $values)
	{
		$res = $task->activeRow->update($values);
		
		//	Update domain object
		foreach ($values as $key => $val) {
			switch ($key) {
				case 'budget_type':
					$task->budgetType = $val;
					break;
				default:
					$task->$key = $val;
			}
		}

		return $task;
	}


	/**
	 * Delete selected task
	 * 
	 * @param  Task   $task
	 */
	public function delete(Task $task)
	{
		$task->activeRow->delete();
	}


	/**
	 * @param Task
	 * @param string $path
	 * @param int $contentType
	 */
	public function saveAttachment(Task $task, $path, $contentType, $size)
	{
		Validators::assert($path, 'string');
		Validators::assert($size, 'int');
		//~ Validators::assert($contentType, 'int');

		try {
			$task->related('task_attachment')
					->insert(array(
							'path' => $path,
							'type_id' => $contentType,
							'size' => $size,
							));
		}
		catch (\PDOException $e) {
			if (! self::ERROR_DUPLICATE_ENTRY == $e->getCode()) {
				throw $e;
			}

			$task->related('task_attachment')
					->select('`task_attachment`.id')
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
		Validators::assert($path, 'string');
		$task->related('task_attachment')
				->where('path', $path)
				->delete();
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
			return Task::createFromActiveRow($row/*, $row->id*/);
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
 	 * Get all promoted tasks
 	 * 
 	 * @param int limit for one page
 	 * @param int offset for current page in paginator
 	 * 
 	 * @return Nette\Database\Table\Selection
 	 */
	public function getPromotedTasks($limit, $offset)
	{
		return $this->getTable()
			->select('task.*, sum(:accepted_task.status <> (4) AND IFNULL(:accepted_task.status,0)) AS finished')
			->where('task.promotion >= (?)', 1)
			->group('task.id')
			->having('finished < task.workers')
			->limit($limit, $offset)
			->order('created DESC');
	}


	/**
	 * Get promoted tasks not assigned to current user
	 * 
	 * @param  int $limit  Paginator limit
	 * @param  int $offset Paginator offset
	 * @param  int $userId User's ID
	 * 
	 * @return Table\Selection         Filtered Results
	 */
	public function getPromotedTasksFilterByUserAccepted($limit, $offset, $userId)
	{
		// Subquery in where(NOT IN (?)) must not be NULL !
		return $this->getTable()
			->select('task.*, sum(:accepted_task.status <> (4) AND IFNULL(:accepted_task.status,0)) AS finished')
			->where('task.id NOT IN (?)', $this->table('accepted_task')->select('task_id')->where('user_id', $userId) )
			->where('task.promotion >= (?)', 1)
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
			->where('task.id NOT IN (?)', $this->table('accepted_task')
												->select('task_id')
												->where('user_id', $userId) )
			->group('task.id')
			->having('finished < task.workers')
			->limit($limit, $offset)
			->order('created DESC');
	}


	/**
	 * Get Promoted Tasks with tag ID
	 * 
	 * @param  string $tag    Tag
	 * @param  int $limit  Paginator limit
	 * @param  int $offset Paginator offset
	 * 
	 * @return Table\Selection         Filtered Results
	 */
	public function getPromotedTaggedTasks($tag, $limit, $offset)
	{
		return $this->getTable()
			->select('task.*, sum(:accepted_task.status <> (4) AND IFNULL(:accepted_task.status,0)) AS finished')
			->where('task.promotion >= (?)', 1)
			->where(':task_has_tag.tag.tag', $tag)
			->group('task.id')
			->having('finished < task.workers')
			->limit($limit, $offset)
			->order('created DESC');
	}


	/**
	 * Get Promoted tasks with tag not assigned to current user
	 * 
	 * @param  string $tag Tag
	 * @param  int $limit  Paginator limit
	 * @param  int $offset Paginator offset
	 * @param  int $userId User's ID
	 * 
	 * @return Table\Selection         Filtered Results
	 */
	public function getPromotedTaggedTasksFilterByUserAccepted($tag, $limit, $offset, $userId)
	{
		// Subquery in where(NOT IN (?)) must not be NULL !
		return $this->getTable()
			->select('task.*, sum(:accepted_task.status <> (4) AND IFNULL(:accepted_task.status,0)) AS finished')
			->where('task.promotion >= (?)', 1)
			->where(':task_has_tag.tag.tag', $tag)
			->where('task.id NOT IN (?)', $this->table('accepted_task')
												->select('task_id')
												->where('user_id', $userId) )
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


	//	PRIVATES


	/**
	 * Check existance of the token (taks) in DB
	 * 
	 * @param  string  $token
	 * 
	 * @return boolean TRUE|FALSE
	 */
	private function isEntryInDatabase($token)
	{
		return $this->getTaskByToken($token) ? TRUE : FALSE;
	}



	/**
	 * Generate unique task ID
	 * 
	 * @return string 36^8 =  ~ 2.8 * 10^12 variations
	 */
	private function generateToken()
	{
		$alpha = str_shuffle("abcdefghijklmnopqrstvwuxyz0123456789");
		$length = 8;
		$row = True;
		while ($row) {
			for($i = 0, $token = '', $l = strlen($alpha) - 1; $i < $length; $i ++) {
				$token .= $alpha{mt_rand(0, $l)};
			}

			// Check if it does not already exist in DB
			$row = $this->isEntryInDatabase($token); //False if not found
		}

		return $token;
	}



}
