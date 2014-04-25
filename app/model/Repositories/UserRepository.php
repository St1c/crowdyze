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
	 * @param  array  	$data 	Data to insert
	 * @return ActiveRow      	Table row of newly inserted data
	 */
	public function create(array $data)
	{
		$data['username'] = $this->generateToken();
		return $this->getTable()->insert($data);
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
	 * Get actual state of account for given user_id
	 * 
	 * @param  int $userId 
	 * @return string 
	 */
	public function getWallet($userId)
	{
		return ($row = $this->getTable()->select('wallet')->where('id', $userId)->fetch()) ? $row->wallet : 0;
	}



	/**
	 * Update balance in user's wallet
	 * 
	 * @param  int $userId
	 * @param  int $balance
	 */
	public function updateWallet($userId, $balance)
	{
		$this->find(array('id' => $userId))->update(array('wallet' => $balance));
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



	/**
	 * Get single user by token
	 * 
	 * @param  string $token
	 * 
	 * @return User
	 */
	public function getUserByToken($token)
	{
		if ($row = $this->getTable()->where('username', $token)->fetch()) {
			return User::createFromActiveRow($row/*, $row->id*/);
		}
	}



	//	PRIVATES



	/**
	 * Check existance of the token (taks) in DB
	 * 
	 * @param  string  $token
	 * 
	 * @return boolean TRUE|FALSE
	 */
	private function isEntryInDatabase($token)
	{
		return $this->getUserByToken($token) ? TRUE : FALSE;
	}



	/**
	 * Generate unique task ID
	 * 
	 * @return string 36^8 =  ~ 2.8 * 10^12 variations
	 */
	private function generateToken()
	{
		$alpha = str_shuffle("abcdefghijklmnopqrstvwuxyz0123456789");
		$length = 8;
		$row = True;
		while ($row) {
			for($i = 0, $token = '', $l = strlen($alpha) - 1; $i < $length; $i ++) {
				$token .= $alpha{mt_rand(0, $l)};
			}

			// Check if it does not already exist in DB
			$row = $this->isEntryInDatabase($token); //False if not found
		}

		return $token;
	}



}
