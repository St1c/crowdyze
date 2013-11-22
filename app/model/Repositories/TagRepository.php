<?php
namespace Model\Repositories;

class TagRepository extends BaseRepository
{

	public function create($tag)
	{
		return $this->getTable()->insert(array('tag' => $tag));
	}

	public function get($tag)
	{
		return $this->getTable()->where(array('tag' => $tag))->fetch(); 
	}

	public function getRelatedTasks($tag)
	{
		return $this->getTable()->where(':task_has_tag.tag.tag', $tag);
	}

}