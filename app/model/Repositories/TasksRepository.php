<?php
namespace Model\Repositories;

use Nette\Database\Table\ActiveRow;

class TasksRepository extends BaseRepository
{
 	/** 
 	 * Create new task
 	 * 
 	 * @param array $task details
 	 * @return Nette\Database\Table\ActiveRow
 	 */
	public function create(array $task)
	{
		return $this->getTable()->insert($task);
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
		return $task->update($values);
	}

	/**
	 * Get single task
	 * 
	 * @param int $id Task ID
	 * @return ActiveRow
	 */
	public function getTask($id)
	{
		return $this->getTable()->where('id', $id)->fetch();
	}

	/** 
 	 * Get all tasks
 	 * 
 	 * @param int limit for one page
 	 * @param int offset for current page in paginator
 	 * @return Nette\Database\Table\Selection
 	 */
	public function getTasks($limit, $offset)
	{
		return $this->getTable()
			->select('tasks.*')
			->limit($limit, $offset)
			->order('created DESC');
	}

	/**
	 * Get tasks not assigned to current user
	 * 
	 * @param  int $limit  Paginator limit
	 * @param  int $offset Paginator offset
	 * @param  int $userId User's ID
	 * @return Table\Selection         Filtered Results
	 */
	public function getTasksFilterByUserAccepted($limit, $offset, $userId)
	{
		// Subquery in where(NOT IN (?)) must not be NULL !
		return $this->getTable()
			->select('tasks.*')
			->where('id NOT IN (?)', $this->table('accepted_tasks')->select('tasks_id')->where('users_id', $userId) )
			->limit($limit, $offset)
			->order('created DESC');
	}

	/** 
 	 * Get task's tags
 	 * 
 	 * @param int task id
 	 * @return Nette\Database\Table\ActiveRow
 	 */
	public function getTaskTags($taskId)
	{
		return $this->getTable()
			->select(':tasks_has_tags.tasks_tags.tag')
			->where('task.id', $taskId);
	}
	
	/**
	 * Get Tasks with tag ID
	 * 
	 * @param  string $tag    Tag
	 * @param  int $limit  Paginator limit
	 * @param  int $offset Paginator offset
	 * @return Table\Selection         Filtered Results
	 */
	public function getTaggedTasks($tag, $limit, $offset)
	{
		return $this->getTable()
			->select('tasks.*')
			->where(':tasks_has_tags.tasks_tags.tag', $tag)
			->limit($limit, $offset)->order('created DESC');
	}

	/**
	 * Get tasks with tag not assigned to current user
	 * 
	 * @param  string $tag Tag
	 * @param  int $limit  Paginator limit
	 * @param  int $offset Paginator offset
	 * @param  int $userId User's ID
	 * @return Table\Selection         Filtered Results
	 */
	public function getTaggedTasksFilterByUserAccepted($tag, $limit, $offset, $userId)
	{
		// Subquery in where(NOT IN (?)) must not be NULL !
		return $this->getTable()
			->select('tasks.*')
			->where(':tasks_has_tags.tasks_tags.tag', $tag)
			->where('tasks.id NOT IN (?)', $this->table('accepted_tasks')->select('tasks_id')->where('users_id', $userId) )
			->limit($limit, $offset)
			->order('created DESC');
	}

	/** 
 	 * Get number of all all tasks
 	 * 
 	 * @return int
 	 */
	public function getCount()
	{
		return $this->getTable()->select('tasks.*')->count('*');
	}

	/**
	 * Get number of tasks with specified tag
	 * 
	 * @param string $tag Tag name
	 * @return ActiveRow Number of tasks with certain tag
	 */
	public function getTagsTasksCount($tag)
	{
		return $this->getTable()
			->select('tasks.*')
			->where(':tasks_has_tags.tasks_tags.tag', $tag)
			->count();
	}

}