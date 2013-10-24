<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class Users_detailsRepository extends BaseRepository
{

	/**
	 * Insert new values in the table
	 * 
	 * @param  array  	$values 	Data to insert
	 * @return ActiveRow         	Table row of newly inserted data
	 */
	public function create(array $values)
	{
		return $this->getTable()->insert($values);
	}

	/**
	 * Update data in DB
	 * 
	 * @param  ActiveRow  	$user   User's DB row
	 * @param  array 		$values User's data
	 * @return ActiveRow        	New record in DB
	 */
	public function update(ActiveRow $user, array $values)
	{
		// TODO Validate values
		$user->update($values);
	}

}