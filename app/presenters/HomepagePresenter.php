<?php

namespace App;

use Nette,
	Model,
	Controls;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BaseSignedPresenter
{
	/** @var Nette\Mail\IMailer @inject */
	public $mailer;

	/** @var Model\Services\TaskService @inject */
	public $taskService;

	/** @var Model\Services\UserService @inject */
	public $userService;


	protected function beforeRender()
	{
		$this->template->activeJobs = $this->userService->getAcceptedUserTasksCount($this->user->id);
	}
	

	public function renderDefault()
	{
		$paginator = $this['paginator']->getPaginator();
		$this['paginator']->paginator->itemCount = $this->taskService->count;
		$this->template->tasks = $this->taskService->getTasks($paginator->itemsPerPage, 
				$paginator->offset, 
				$this->getUser()->id
				);
	}


	public function actionTag($id)
	{
		$paginator = $this['paginator']->getPaginator();
		$this['paginator']->paginator->itemCount = $this->taskService->getTagsTasksCount($id);

		$this->template->setFile(__DIR__ . '/../templates/Homepage/default.latte');
		$this->template->tasks = $this->taskService->getTaggedTasks($id, 
				 $paginator->itemsPerPage, 
				 $paginator->offset, 
				 $this->getUser()->id
				 );
	}



	protected function createComponentPaginator()
	{
		$paginator = new \Controls\PaginatorControl();
		$paginator->paginator->itemsPerPage = 24;
		return $paginator;
	}


}
