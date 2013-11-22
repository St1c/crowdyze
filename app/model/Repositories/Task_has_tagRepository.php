<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class Task_has_tagRepository extends BaseRepository
{

	public function create(array $data)
	{
		return $this->getTable()->insert($data);
	}

}