<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class Accepted_taskRepository extends BaseRepository
{


	/**
	 * Insert accepted task to DB
	 * 
	 * @param int User ID
	 * @param int Task ID
	 * @param int 1=accepted|2=pending|3=satisfied|4=unsatisfied
	 * 
	 * @return ActiveRow
	 */
	public function insert($userId, $taskId, $status)
	{
		return $this->getTable()->insert(array(
			'user_id' 	=> $userId,
			'task_id' 	=> $taskId,
			'status' 	=> (int) $status,
			'payment'	=> 20
		));
	}


	/**
	 * Get status of the accepted task
	 * 
	 * @param int User ID
	 * @param int Task ID
	 * 
	 * @return string 'accepted'|'finished'|'satisfied'|'unsatisfied'|FALSE
	 */
	public function getStatusById($userId, $taskId)
	{
		return $this->getTable()
			->select('status.status')
			->where('user_id', $userId)
			->where('task_id', $taskId)->fetch();
	}


	/**
	 * Get accpeted user's task
	 * 
	 * @param  int $userId 	User's ID
	 * 
	 * @return ActiveRow 	User's accepted unfinished tasks
	 */
	public function getAcceptedByUser($userId)
	{
		return $this->getTable()
			->select('task.*')
			->where('accepted_task.user_id', $userId)
			->where('accepted_task.status', 1);
	}


	/**
	 * Get user's finished tasks 
	 * 
	 * @param  int $userId 	User ID
	 * 
	 * @return ActiveRow 	User's finished tasks
	 */
	public function getFinishedByUser($userId)
	{
		return $this->getTable()
			->select('task.*')
			->where('accepted_task.user_id', $userId)
			->where('accepted_task.status', 2);
	}


	/**
	 * Get user's satisfied tasks - paid
	 * 
	 * @param  int $userId 	User ID
	 * 
	 * @return ActiveRow 	User's satisfied tasks
	 */
	public function getSatisfiedByUser($userId)
	{
		return $this->getTable()
			->select('task.*')
			->where('accepted_task.user_id', $userId)
			->where('accepted_task.status', 3);
	}


	/**
	 * Get user's unsatisfied tasks - not paid
	 * 
	 * @param  int $userId 	User ID
	 * 
	 * @return ActiveRow 	User's unsatisfied tasks
	 */
	public function getUnsatisfiedByUser($userId)
	{
		return $this->getTable()
			->select('task.*')
			->where('accepted_task.user_id', $userId)
			->where('accepted_task.status', 4);
	}


	/**
	 * Number of task assigned to a given user
	 * 
	 * @param  int 	$userId User's Id
	 * 
	 * @return int  Resulting number
	 */
	public function getUsersNumberOfAssignedTasks($userId)
	{
		return $this->getTable()
			->where('user_id', $userId)
			->count();
	}


	/**
	 * Obtain info about state for the current task - accepted/not accepted by current user
	 * 
	 * @param  int  $taskId
	 * @param  int  $userId
	 * 
	 * @return boolean TRUE|FALSE
	 */
	public function isAccepted($taskId, $userId)
	{
		return $this->getTable()->where('task_id', $taskId)->where('user_id', $userId)->fetch();
	}
}