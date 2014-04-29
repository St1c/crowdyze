<?php 

namespace App;


use Nette,
	Model\Services\TaskService;


class SearchPresenter extends BaseSignedPresenter 
{
	
	/** @var Model\Services\SearchService @inject */
	public $searchService;


	/** @var Model\Services\UserService @inject */
	public $userService;



	/**
	 * Find task by key.
	 */
	public function actionDefault($q)
	{
		$paginator = $this['paginator']->getPaginator();
		$this['paginator']->paginator->itemCount = $this->searchService->countBy($q);


		$this->template->queryString = $q;

		//	Split to promoted and other tasks.
		$promoted = array();
		$other = array();
		foreach ($this->searchService->findBy($q, $paginator->itemsPerPage, $paginator->offset) as $row) {
			if ($row->promotion) {
				$promoted[] = $row;
			}
			else {
				$other[] = $row;
			}
		}
		
		$this->template->promotedResults = $promoted;
		$this->template->otherResults = $other;
	}



	protected function createComponentPaginator()
	{
		$paginator = new \Controls\PaginatorControl();
		$paginator->paginator->itemsPerPage = self::ITEMS_PER_PAGE;
		return $paginator;
	}

}
