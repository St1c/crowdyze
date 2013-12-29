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

	/** @var Model\Services\PayService @inject */
	public $payService;

	/** @var Controls\IAddTaskControlFactory @inject */
	public $addTaskControlFactory;

	/** @var Controls\IEditTaskControlFactory @inject */
	public $editTaskControlFactory;



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



	public function actionDetail($token)
	{
		$task = $this->redirectIfEmpty(
				$this->taskService->getTaskByToken(
						$this->redirectIfEmpty($token, $token)), 
				$token);
		$this->template->task = $task;
		$this->template->userId = $this->getUser()->id;
		$this->template->accepted = $this->taskService->isAccepted($task->token, $this->getUser()->id);
		// $this->template->owner = $this->taskService->getOwnerTasks($this->getUser()->id);
	}



	/**
	 * Adding new task routine
	 */
	public function actionAdd()
	{
		if (!$this->payService->getBalance($this->user->id) > 0) {
			$this->redirect('Wallet:deposit');
		}
	}



	/**
	 * @param string $token
	 */
	public function actionEdit($token)
	{
		$task = $this->redirectIfEmpty($this->taskService->getTaskByToken($this->redirectIfEmpty($token, $token)), $token);
		$this->template->task = $task;
		$this->template->userId = $this->getUser()->id;
		$this->template->accepted = $this->taskService->isAccepted($task->token, $this->getUser()->id);
		// $this->template->owner = $this->taskService->getOwnerTasks($this->getUser()->id);
	}



	protected function beforeRender()
	{
		$this->template->activeJobs = $this->userService->getAcceptedUserTasksCount($this->user->id);
	}



	/**
	 * Acceptance task as worker.
	 * @param string $token Ident of task.
	 */
	public function handleAcceptTask($token)
	{
		try {
			$this->taskService->acceptTask($this->getUser()->id, $token);
			$this->template->accepted = $this->taskService->isAccepted($token, $this->getUser()->id);
		}
		catch (\Exception $e) {
			$this->flashMessage($e->getMessage(), 'alert-danger');
		}

		$this->redirect('this');
	}



	protected function createComponentPaginator()
	{
		$paginator = new \Controls\PaginatorControl();
		$paginator->paginator->itemsPerPage = self::ITEMS_PER_PAGE;
		return $paginator;
	}



	/**
	 * Add Task From control factory
	 * 
	 * @return 	\Nette\Application\UI\Control AddTaskControl
	 */
	protected function createComponentAddTask()
	{
		return $this->addTaskControlFactory->create();
	}



	protected function createComponentEditTask()
	{
		return $this->editTaskControlFactory->create();
	}



	/**
	 * Asserting $value by empty and if is empty, than redirect to default action of presenter.
	 * 
	 * @param $value Asserted value.
	 * @param $token Added value for message.
	 */
	private function redirectIfEmpty($value, $token)
	{
		if (!$value) {
			$this->flashMessage("notice.error.task-not-found", 'alert-danger', NULL, array('token' => $token));
			$this->redirect('default');
		}
		return $value;
	}


}
