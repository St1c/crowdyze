<?php
namespace Model\Repositories;

class EmailRepository extends BaseRepository
{

	/**
	 * Add new answer from the form
	 * 
	 * @param array $record
	 * @return  ActiveRow
	 */
	public function create($record)
	{
		return $this->getTable()->insert($record);
	}


	/**
	 * Find existing email in DB
	 * 
	 * @param  string $email
	 * @return ActiveRow|FALSE
	 */
	public function find($email)
	{
		return $this->getTable()->where('email', $email)->fetch();
	}
}