<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class Budget_typeRepository extends BaseRepository
{
	public function getAll()
	{
		return $this->getTable()->fetchPairs('id', 'budget_type');
	}
}