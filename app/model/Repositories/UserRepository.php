<?php
namespace Model\Repositories;

use Nette,
	Nette\Database\Connection,
	Nette\Database\Table\ActiveRow,
	Nette\Security\AuthenticationException,
	Nette\Security\Identity;

class UserRepository extends BaseRepository
{

	/**
	 * Get user's data
	 * 
	 * @param  array 	$by 	Searching attributes
	 * @return ActiveRow    	User's data
	 */
	public function find(array $by)
	{
		return $this->getTable()->where($by)->fetch();
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
		return $user->update($values);
	}

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
	 * Set user password
	 * 
	 * @param int 		$id       User's id
	 * @param string 	$password Password
	 */
	public function setPassword($id, $password)
	{
	    $this->getTable()->where(array('id' => $id))->update(array(
	        'password' => sha1($password)
	    ));
	}

	/**
	 * Create user's Identity
	 * 
	 * @param  ActiveRow $user User's details
	 * @return Identity        User's identity
	 */
	public function createIdentity(ActiveRow $user)
	{
		$data = $user->toArray();
		unset($data['password']);
		return new Nette\Security\Identity($user->id, $user->role, $data);
	}
}