<?php

namespace Model\Services;


use Nette;
use	Model\Repositories,
	Model\Services,
	Model\Domains;


class DiscussService extends Nette\Object
{

	private $discussRepository;


	/**
	 * DI
	 */
	public function __construct(
			Repositories\DiscussRepository $discussRepository
			)
	{
		$this->discussRepository = $discussRepository;
	}



	/**
	 * Create new comment.
	 * 
	 * @param Discuss entry
	 * 
	 * @return Discuss
	 */
	public function createComment(Domains\Discuss $entry)
	{
		$data = array(
				'task_id' => $entry->task->id,
				'user_id' => $entry->author->id,
				'body' => strip_tags($entry->content),
				);

		$activeRow = $this->discussRepository->create($data);
		return Domains\Discuss::createFromEntry($entry, $activeRow->id, new \DateTime());
	}




}
