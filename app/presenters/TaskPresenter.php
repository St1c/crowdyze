<?php 

namespace App;

use Nette,
	Nette\Application\UI\Form,
	Model\Services\TaskService;

class TaskPresenter extends BaseSignedPresenter 
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



	public function actionDefault($filter = Null)
	{
		$paginator = $this['paginator']->getPaginator();
		if ($filter) {
			$this['paginator']->paginator->itemCount = $this->taskService->getTagsTasksCount($filter);
			$this->template->tasks = $this->taskService->getTaggedTasks($filter,
					 $paginator->itemsPerPage, 
					 $paginator->offset, 
					 $this->getUser()->id
					 );
		}
		else {
			$this['paginator']->paginator->itemCount = $this->taskService->count;
			$this->template->tasks = $this->taskService->getTasks($paginator->itemsPerPage, 
					$paginator->offset, 
					$this->getUser()->id
					);
		}
	}



	public function actionDetail($id)
	{
		$task = self::redirectByEmpty($this->taskService->getTaskByToken($id), $id);
		$this->template->task = $task;
		$this->template->userId = $this->getUser()->id;
		$this->template->accepted = $this->taskService->isAccepted($task->token, $this->getUser()->id);
		// $this->template->owner = $this->taskService->getOwnerTasks($this->getUser()->id);
	}



	/**
	 * @param string $id
	 */
	public function actionEdit($id)
	{
		$task = self::redirectByEmpty($this->taskService->getTaskByToken($id), $id);
		$this->template->task = $task;
		$this->template->userId = $this->getUser()->id;
		$this->template->accepted = $this->taskService->isAccepted($task->token, $this->getUser()->id);
		// $this->template->owner = $this->taskService->getOwnerTasks($this->getUser()->id);
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



	/**
	 * Asserting $value by empty and if is empty, than redirect to default action of presenter.
	 * 
	 * @param $value Asserted value.
	 * @param $id Added value for message.
	 */
	private static function redirectByEmpty($value, $id)
	{
		if (!$value) {
			$this->flashMessage("Task with ID: $id was not found, or removed...");
			$this->redirect('default');
		}
		return $value;
	}


}
