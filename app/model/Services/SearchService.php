<?php

namespace Model\Services;


use Nette;
use	Model\Repositories,
	Model\Services,
	Model\Domains;


class SearchService extends Nette\Object
{

	/** @var searchRepository */
	private $searchRepository;
	
	
	/**
	 * DI
	 */
	public function __construct(
			Repositories\SearchRepository $searchRepository
			)
	{
		$this->searchRepository = $searchRepository;
	}



	/**
	 * Get result of searching.
	 * 
	 * @param string $queryString
	 * 
	 * @return int
	 */
	public function countBy($queryString)
	{
		return $this->searchRepository->countBy($queryString);
	}



	/**
	 * Get result of searching.
	 * 
	 * @param string $queryString
	 * @param int $limit
	 * @param int $offset
	 * 
	 * @return array of 
	 */
	public function findBy($queryString, $limit = 20, $offset = 0)
	{
		return $this->searchRepository->findBy($queryString, $limit, $offset);
	}



}
