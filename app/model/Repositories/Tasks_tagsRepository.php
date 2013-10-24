<?php
namespace Model\Repositories;

class Tasks_tagsRepository extends BaseRepository
{

	private function create($tag)
	{
		return $this->getTable()->insert(array('tag' => $tag));
	}

	private function get($tag)
	{
		return $this->getTable()->where(array('tag' => $tag))->fetch(); 
	}

	public function getRelatedTasks($tag)
	{
		return $this->getTable()->where(':tasks_has_tags.tasks_tags.tag', $tag);
	}

}