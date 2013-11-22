<?php
namespace Model\Repositories;

class PollRepository extends BaseRepository
{

	/**
	 * Add new answer from the form
	 * 
	 * @param array $record
	 * @return  ActiveRow
	 */
	public function create($record)
	{
		return $this->getTable()->insert($record);
	}


	public function find($uuid)
	{
		return $this->getTable()->where('uuid', $uuid)->fetch();
	}

}