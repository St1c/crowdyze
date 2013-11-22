<?php 

namespace App;

use Nette,
	Nette\Application\UI\Form,
	Model\Services\TaskService;

class OverviewPresenter extends BasePresenter 
{
	/** @var Model\Services\TaskService @inject */
	public $taskService;
	/** @var Model\Services\UserService @inject */
	public $userService;
	/** @var Model\Repositories\Budget_typeRepository @inject */
	public $budget_typeRepository;
	/** @var Model\Repositories\Department_nameRepository @inject */
	public $department_nameRepository;
	/** @var Controls\ISingleTaskControlFactory @inject */
	public $singleTaskControlFactory;

	/** @var bool $edit */
	public $edit;

	protected function startup()
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:', array( 'backlink' => $this->storeRequest() ));
		} else {
			$this->backlink = NULL;
		}
	}

	protected function beforeRender()
	{
		$this->template->activeJobs = $this->userService->getAcceptedUserTasksCount($this->user->id);
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
		$this['paginator']->paginator->itemCount = $this->taskService->count;
		$this->template->tasks = $this->taskService->getTasks( $paginator->itemsPerPage, 
																$paginator->offset, 
																$this->getUser()->id);
	}

	public function actionTask($id)
	{
		$task = $this->taskService->getTaskByToken($id);

		if (!$task) {
			$this->flashMessage("Task with ID: $id was not found, or removed...");
			$this->redirect('Overview:');
		}

		$this->template->edit 		= $this->edit;
		$this->template->task 		= $task;
		$this->template->userId 	= $this->getUser()->id;
		$this->template->accepted	= $this->taskService->isAccepted($task->token, $this->getUser()->id);
		// $this->template->owner		= $this->taskService->getOwnerTasks($this->getUser()->id);
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
		$this['paginator']->paginator->itemCount = $this->taskService->getTagsTasksCount($id);

		$this->template->setFile(__DIR__ . '/../templates/Overview/default.latte');
		$this->template->tasks = $this->taskService->getTaggedTasks($id, 
																	 $paginator->itemsPerPage, 
																	 $paginator->offset, 
																	 $this->getUser()->id);
	}

	public function handleAcceptTask($id)
	{
		try {
			$this->taskService->acceptTask($this->getUser()->id, $id);
			$this->template->accepted = $this->taskService->isAccepted($id, $this->getUser()->id);
		} catch (\Exception $e) {
			$this->flashMessage($e->getMessage(), 'alert-danger');
		}
	}

}