<?php
namespace Model\Repositories;

class QuestionRepository extends BaseRepository
{

	/**
	 * Get Id of the question
	 * 
	 * @param string $tag
	 * @return  ActiveRow
	 */
	public function getByTag($tag)
	{
		return $this->getTable()->where('tag', $tag)->fetch();
	}

}