<?php
namespace Model\Repositories;


use Nette;


class DiscussRepository extends BaseRepository
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
	 * Insert new values in the table
	 * 
	 * @param  array  	$values 	Data to insert
	 * @return ActiveRow         	Table row of newly inserted data
	 */
	public function create(array $values)
	{
		return $this->getTable()->insert($values);
	}


}
