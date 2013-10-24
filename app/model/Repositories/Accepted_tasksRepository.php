<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class Accepted_tasksRepository extends BaseRepository
{

	/**
	 * Insert accepted task to DB
	 * @param int User ID
	 * @param int Task ID
	 * @param int 1=accepted|2=finished|3=satisfied|4=unsatisfied
	 * @return ActiveRow
	 */
	public function insert($userId, $taskId, $status)
	{
		return $this->getTable()->insert(array(
			'users_id' 	=> $userId,
			'tasks_id' 	=> $taskId,
			'status' 	=> (int) $status,
			'payment'	=> 20
		));
	}

	/**
	 * Get status of the accepted task
	 * @param int User ID
	 * @param int Task ID
	 * @return string 'accepted'|'finished'|'satisfied'|'unsatisfied'|FALSE
	 */
	public function getStatusById($userId, $taskId)
	{
		return $this->getTable()
			->select('status.status')
			->where('users_id', $userId)
			->where('tasks_id', $taskId)->fetch();
	}

	/**
	 * Get accpeted user's task
	 * @param  int $userId 	User's ID
	 * @return ActiveRow 	User's accepted unfinished tasks
	 */
	public function getAcceptedByUser($userId)
	{
		return $this->getTable()
			->select('tasks.*')
			->where('accepted_tasks.users_id', $userId)
			->where('accepted_tasks.status', 1);
	}

	/**
	 * Get user's finished tasks 
	 * @param  int $userId 	User ID
	 * @return ActiveRow 	User's finished tasks
	 */
	public function getFinishedByUser($userId)
	{
		return $this->getTable()
			->select('tasks.*')
			->where('accepted_tasks.users_id', $userId)
			->where('accepted_tasks.status', 2);
	}

	/**
	 * Get user's satisfied tasks - paid
	 * @param  int $userId 	User ID
	 * @return ActiveRow 	User's satisfied tasks
	 */
	public function getSatisfiedByUser($userId)
	{
		return $this->getTable()
			->select('tasks.*')
			->where('accepted_tasks.users_id', $userId)
			->where('accepted_tasks.status', 3);
	}

	/**
	 * Get user's unsatisfied tasks - not paid
	 * @param  int $userId 	User ID
	 * @return ActiveRow 	User's unsatisfied tasks
	 */
	public function getUnsatisfiedByUser($userId)
	{
		return $this->getTable()
			->select('tasks.*')
			->where('accepted_tasks.users_id', $userId)
			->where('accepted_tasks.status', 4);
	}

	/**
	 * Number of task assigned to a given user
	 * @param  int 	$userId User's Id
	 * @return int  Resulting number
	 */
	public function getUsersNumberOfAssignedTasks($userId)
	{
		return $this->getTable()
			->where('users_id', $userId)
			->count();
	}
}