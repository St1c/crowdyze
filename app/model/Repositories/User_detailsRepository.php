<?php
namespace Model\Repositories;

use Nette\Database\Connection,
	Nette\Database\Table\ActiveRow;

class User_detailsRepository extends BaseRepository
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

	/**
	 * Check if the details for given user exist
	 * @param  int    $user_id User ID
	 * @return bool            1|FALSE
	 */
	public function detailsExists($user_id)
	{
		return $this->getTable()->where('user_id', $user_id)->fetch();
	}
}