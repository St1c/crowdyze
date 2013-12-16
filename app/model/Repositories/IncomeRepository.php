<?php
namespace Model\Repositories;

class IncomeRepository extends BaseRepository
{

	/**
	 * Insert new values in the table
	 * 
	 * @param  array  	$values 	Data to insert
	 * @return ActiveRow         	Table row of newly inserted data
	 */
	public function create($values)
	{
		return $this->getTable()->insert($values);
	}



	/**
	 * @param  array 	$by 	Searching attributes
	 * @return ActiveRow    	User's data
	 */
	public function get(array $by)
	{
		return $this->getTable()->where($by)->fetch(); 
	}

}
