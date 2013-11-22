<?php
namespace Model\Repositories;

use Nette,
	Nette\Database\Connection,
	Nette\Database\Table\ActiveRow,
	Nette\Security\AuthenticationException,
	Nette\Security\Identity;

class WalletRepository extends BaseRepository
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
	 * Get user's data
	 * 
	 * @param  array 	$by 	Searching attributes
	 * @return ActiveRow    	User's data
	 */
	public function read(array $by)
	{
		return $this->getTable()->where($by)->fetch();
	}

	/**
	 * Update data in DB
	 * 
	 * @param  ActiveRow  	$wallet   	User's DB row
	 * @param  array 		$values 	User's data
	 * @return ActiveRow        		New record in DB
	 */
	public function update(ActiveRow $wallet, array $values)
	{
		// TODO Validate values
		return $wallet->update($values);
	}


	/**
	 * Delete wallet belonging to user_id
	 * 
	 * @param  int $userId
	 * @return int number of affected rows or FALSE in case of an error
	 */
	public function delete($userId)
	{
		return $this->getTable()->where('user_id', $userId)->delete();
	}


	/**
	 * Get actual state of account for given user_id
	 * 
	 * @param  int $userId 
	 * @return string 
	 */
	public function getBalance($userId)
	{
		return $this->getTable()->select('balance')->where('user_id', $userId)->fetch();
	}
}