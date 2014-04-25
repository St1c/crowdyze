<?php

namespace Model\Services;


use Nette;
use	Model\Repositories,
	Model\Services,
	Model\Domains\Comment;


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


}
