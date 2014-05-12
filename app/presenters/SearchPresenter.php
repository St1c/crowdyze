<?php

namespace App;


use Nette,
	Model\Services\TaskService;


class SearchPresenter extends BaseSignedPresenter
{

	/**
	 * Maximální počet promopoložek na stránce, které se zobrazují ve velkém boxu.
	 */
	const PROMO_COUNT = 15;
	
	
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
		$counter = 0;
		foreach ($this->searchService->findBy($q, $paginator->itemsPerPage, $paginator->offset) as $row) {
			if ($counter++ < self::PROMO_COUNT && $row->promotion) {
				$promoted[] = $row;
			}
			else {
				$other[] = $row;
			}
		}

		$this->template->promotedResults = $promoted;
		$this->template->otherResults = $other;
	}



	public function renderDefault($q)
	{
		if ($this->isAjax()) {
			$paginator = $this['paginator']->getPaginator();
			if ($paginator->page <= 1) {
				$this->redrawControl('tasks-promoted');
			}
			$this->redrawControl('tasks-other');
			$this->redrawControl('notifier');
		}
	}



	protected function createComponentPaginator()
	{
		$paginator = new \Controls\PaginatorControl();
		$paginator->paginator->itemsPerPage = self::ITEMS_PER_PAGE;
		return $paginator;
	}

}
