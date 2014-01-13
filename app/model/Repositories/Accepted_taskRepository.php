<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class Accepted_taskRepository extends BaseRepository
{


	const STATUS_ACCEPTED = 1;
	const STATUS_PENDING = 2;
	const STATUS_SATISFIED = 3;
	const STATUS_UNSATISFIED = 4;


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
			'status' 	=> (int) $status
		));
	}


	/**
	 * Update accepted task record
	 * 
	 * @param int 	$taskId 
	 * @param int 	$userId
	 * @param array $values
	 */
	public function update($taskId, $userId, array $values)
	{
		$entry = $this->getTable()
			->where('task_id', $taskId)
			->where('user_id', $userId);
		$entry->update($values);
		return $entry->fetch();
	}



	/**
	 * Get all pending results for given taskId
	 * 
	 * @param  int $taskId
	 * 
	 * @return Database\Selection
	 */
	public function getPending($taskId)
	{
		return $this->getTable()
			->where('task_id',$taskId)
			->where('status', self::STATUS_PENDING)
			->fetchAll();
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
		return $this->table('task')
			->select('task.*')
			->where(':accepted_task.status', self::STATUS_ACCEPTED)
			->where(':accepted_task.user_id', $userId)
			->order('deadline DESC');
	}


	/**
	 * Get user's finished tasks - pending, satisfied and unsatisfied 
	 * 
	 * @param  int $userId 	User ID
	 * 
	 * @return ActiveRow 	User's finished tasks
	 */
	public function getFinishedByUser($userId)
	{
		return $this->getTable()
			->select('task.*, accepted_task.status')
			->where('accepted_task.user_id', $userId)
			->where('accepted_task.status >= (?)', self::STATUS_PENDING);
	}


	/**
	 * Get user's tasks where result has been sent, but not yet valued
	 * 
	 * @param  int $userId 	User ID
	 * 
	 * @return ActiveRow 	User's finished tasks
	 */
	public function getPendingByUser($userId)
	{
		return $this->getTable()
			->select('task.*')
			->where('accepted_task.user_id', $userId)
			->where('accepted_task.status', self::STATUS_PENDING);
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
			->where('accepted_task.status', self::STATUS_SATISFIED);
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
			->where('accepted_task.status', self::STATUS_UNSATISFIED);
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
	 * Update accepted task status to Pending | Satisfied | Unsatisfied
	 * 
	 * @param  int $taskId 
	 * @param  int $userId
	 * @param  int $status 1=accepted|2=pending|3=satisfied|4=unsatisfied
	 */
	public function updateStatus($taskId, $userId, $status)
	{
		$this->getTable()
			->where('task_id', $taskId)
			->where('user_id', $userId)
			->update(array(
				'status' => $status
			));
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
		return $this->getTable()
			->where('task_id', $taskId)
			->where('user_id', $userId)
			->fetch() 
			? TRUE : FALSE;
	}


	/**
	 * Obtain info about state for the current task - accepted/not accepted by current user
	 * 
	 * @param  int  $taskId
	 * @param  int  $userId
	 * @param  int  $status 1=accepted|2=pending|3=satisfied|4=unsatisfied
	 * 
	 * @return boolean TRUE|FALSE
	 */
	public function isAcceptedFilterByStatus($taskId, $userId, $status)
	{
		return $this->getTable()
			->where('task_id', $taskId)
			->where('user_id', $userId)
			->where('status', $status)
			->fetch() ? TRUE : FALSE;
	}





}
