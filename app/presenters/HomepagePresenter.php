<?php

namespace App;

use Nette,
	Model,
	Controls;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
	/** @var Controls\IAddTaskControlFactory @inject */
	public $addTaskControlFactory;
	/** @var Nette\Mail\IMailer @inject */
	public $mailer;
	/** @var Model\Services\TaskService @inject */
	public $taskService;
	/** @var Model\Services\UserService @inject */
	public $userService;

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


	public function actionTag($id)
	{
		$paginator = $this['paginator']->getPaginator();
		$this['paginator']->paginator->itemCount = $this->taskService->getTagsTasksCount($id);

		$this->template->setFile(__DIR__ . '/../templates/Homepage/default.latte');
		$this->template->tasks = $this->taskService->getTaggedTasks($id, 
																	 $paginator->itemsPerPage, 
																	 $paginator->offset, 
																	 $this->getUser()->id);
	}


	protected function createComponentAddTask()
	{
		return $this->addTaskControlFactory->create();
	} 

	protected function createComponentForm()
	{
		$form = new Nette\Application\UI\Form;
		$form->setTranslator($this->translator);

		$form->addText('from')
			->setAttribute('class', 'form-control')
			->setAttribute('placeholder', 'email');
		$form->addTextarea('text')
			->setAttribute('class', 'form-control')
			->setAttribute('placeholder', 'Message');
		$form->addSubmit('send', 'Send')
			->setAttribute('class', 'btn btn-primary');

		$form->onSuccess[] = $this->sendEmails;
		return $form;
	}

	public function sendEmails($form)
	{
		$message 	= $form->values->text;
		$email 		= $form->values->from;
		
		
		$mail = new Nette\Mail\Message;
		$mail->addTo($email)
			->setBody($message);

		$this->mailer->send($mail);
		
	}

}
