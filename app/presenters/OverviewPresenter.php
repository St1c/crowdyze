<?php 

namespace App;

use Nette,
	Nette\Application\UI\Form,
	Model\Services\TasksService;

class OverviewPresenter extends BasePresenter 
{
	/** @var Model\Services\TasksService @inject */
	public $tasksService;
	/** @var Model\Repositories\Tasks_budget_typesRepository @inject */
	public $tasks_budget_typesRepository;
	/** @var Model\Repositories\Departments_namesRepository @inject */
	public $departments_namesRepository;
	/** @var Controls\ISingleTaskControlFactory @inject */
	public $singleTaskControlFactory;

	/** @var bool $edit */
	public $edit;

	protected function startup()
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Signup:', array( 'backlink' => $this->storeRequest() ));
		} else {
			$this->backlink = NULL;
		}
	}
	
	protected function createComponentPaginator()
	{
		$paginator = new \Controls\PaginatorControl();
		$paginator->paginator->itemsPerPage = 24;
		return $paginator;
	}

	public function actionDefault()
	{
		$paginator = $this['paginator']->getPaginator();
		$this['paginator']->paginator->itemCount = $this->tasksService->count;
		$this->template->tasks = $this->tasksService->getTasks( $paginator->itemsPerPage, 
																$paginator->offset, 
																$this->getUser()->id);
	}

	public function actionTask($id)
	{
		$this->template->edit 	= $this->edit;
		$this->template->task 	= $this->tasksService->getTask($id);
		$this->template->userId = $this->getUser()->id;
	}

	protected function createComponentSingleTask()
	{
		return $this->singleTaskControlFactory->create();
	}

	public function handleEdit($edit)
	{
		$this->edit = TRUE;
		$this->template->edit = $this->edit;
		$this->validateControl();
		$this->invalidateControl('task');
	}

	public function actionTag($id)
	{
		$paginator = $this['paginator']->getPaginator();
		$this['paginator']->paginator->itemCount = $this->tasksService->getTagsTasksCount($id);

		$this->template->setFile(__DIR__ . '/../templates/Overview/default.latte');
		$this->template->tasks = $this->tasksService->getTaggedTasks($id, 
																	 $paginator->itemsPerPage, 
																	 $paginator->offset, 
																	 $this->getUser()->id);
	}

	public function handleAcceptTask($id)
	{
		try {
			$this->tasksService->acceptTask($this->getUser()->id, $id);
		} catch (\Exception $e) {
			$this->flashMessage($e->getMessage(), 'alert-danger');
		}
	}

}