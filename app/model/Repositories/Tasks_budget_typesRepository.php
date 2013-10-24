<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class Tasks_budget_typesRepository extends BaseRepository
{
	public function getAll()
	{
		return $this->getTable()->fetchPairs('id', 'budget_type');
	}
}