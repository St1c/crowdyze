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

}