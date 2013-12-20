<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class ResultRepository extends BaseRepository
{


	public function create(array $values)
	{
		return $this->getTable()->insert($values);
	}


	/**
	 * Get all results for given taskId
	 * 
	 * @param  int $taskId
	 * 
	 * @return Database\Selection
	 */
	public function getAll($taskId)
	{
		return $this->getTable()->where('task_id',$taskId)->fetchAll();
	}

}