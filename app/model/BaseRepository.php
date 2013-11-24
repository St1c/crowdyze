<?php 
namespace Model\Repositories;

use Nette;

/** 
 * Basic operation on database tables
 */

 abstract class BaseRepository extends Nette\Object
 {
 	/** @var Nette\Database\Connection */
 	protected $connection;

 	public function __construct(Nette\Database\Connection $db)
 	{
 		$this->connection = $db;
 	}

 	/**
	 * Return object representing database table
	 * @return Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return $this->connection->getContext()->table($this->getTableName());
	}

	protected function getTableName()
	{
		// Name of the table is extracted from the repository name
		preg_match('#(\w+)Repository$#', get_class($this), $m);
		return lcfirst($m[1]);
	}

	protected function table($name)
	{
		return $this->connection->getContext()->table($name);
	}
 }