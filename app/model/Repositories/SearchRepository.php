<?php

namespace Model\Repositories;


use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;


class SearchRepository extends BaseRepository
{


	/**
	 * Get result of searching.
	 * 
	 * @param string $queryString
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @return array of task
	 */
	public function findBy($queryString, $limit, $offset)
	{
		$res = $this->prepareQueryBy($queryString)
			->order('task.promotion DESC, task.created DESC')
			->limit($limit, $offset)
			;
		return $res;
	}



	/** 
 	 * Get count items of searching.
 	 * 
 	 * @return int
 	 */
	public function countBy($queryString)
	{
		return $this->prepareQueryBy($queryString)->count('*');
	}



	/** 
 	 * Get count items of searching.
 	 * 
 	 * @return <query>
 	 */
	private function prepareQueryBy($queryString)
	{
		$res = $this->connection->getContext()
			->table('task')
			->select('task.*, sum(:accepted_task.status <> (4) AND IFNULL(:accepted_task.status,0)) AS finished')
			->where('(task.title LIKE ?) OR (:task_has_tag.tag.tag = ?)', '%' . $queryString . '%', $queryString)
			->group('task.id')
			->having('finished < task.workers')
			;
		return $res;
	}

}
