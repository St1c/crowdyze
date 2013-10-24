<?php
namespace Model\Repositories;

use Nette;

class Departments_namesRepository extends BaseRepository
{

	public function getAll($userId)
	{
		return $this->getTable()->where('owner', $userId)->fetchPairs('id', 'name');
	}

}