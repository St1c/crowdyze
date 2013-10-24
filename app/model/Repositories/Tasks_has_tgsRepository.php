<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class Tasks_has_tagsRepository extends BaseRepository
{

	public function create(array $data)
	{
		return $this->getTable()->insert($data);
	}

}