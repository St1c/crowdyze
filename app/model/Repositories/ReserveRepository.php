<?php
namespace Model\Repositories;

class ReserveRepository extends BaseRepository
{

	public function create($value)
	{
		return $this->getTable()->insert(array('' => $value));
	}

	public function get($value)
	{
		return $this->getTable()->where(array('' => $value))->fetch(); 
	}

}