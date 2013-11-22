<?php
namespace Model\Repositories;

use Nette;

class Department_nameRepository extends BaseRepository
{

	public function getAll($userId)
	{
		return $this->getTable()->where('owner', $userId)->fetchPairs('id', 'name');
	}

}