<?php 

namespace App;

use Nette,
	Nette\Application\UI\Form,
	Model\Services\TaskService;

class TaskPresenter extends BaseSignedPresenter 
{
	
	const ITEMS_PER_PAGE = 24;
	
	
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



	public function actionDefault()
	{
		$this->redirect('Homepage:');
	}



	public function actionDetail($id)
	{
		$task = $this->taskService->getTaskByToken($id);

		if (!$task) {
			$this->flashMessage("Task with ID: $id was not found, or removed...");
			$this->redirect('Homepage:');
		}

		$this->template->edit 		= $this->edit;
		$this->template->task 		= $task;
		$this->template->userId 	= $this->getUser()->id;
		$this->template->accepted	= $this->taskService->isAccepted($task->token, $this->getUser()->id);
		// $this->template->owner		= $this->taskService->getOwnerTasks($this->getUser()->id);
	}


	protected function beforeRender()
	{
		$this->template->activeJobs = $this->userService->getAcceptedUserTasksCount($this->user->id);
	}



	public function handleAcceptTask($id)
	{
		try {
			$this->taskService->acceptTask($this->getUser()->id, $id);
			$this->template->accepted = $this->taskService->isAccepted($id, $this->getUser()->id);
		}
		catch (\Exception $e) {
			$this->flashMessage($e->getMessage(), 'alert-danger');
		}
	}


	public function handleEdit($edit)
	{
		$this->edit = TRUE;
		$this->template->edit = $this->edit;
		$this->validateControl();
		$this->invalidateControl('task');
	}



	protected function createComponentPaginator()
	{
		$paginator = new \Controls\PaginatorControl();
		$paginator->paginator->itemsPerPage = self::ITEMS_PER_PAGE;
		return $paginator;
	}



	protected function createComponentSingleTask()
	{
		return $this->singleTaskControlFactory->create();
	}


}
