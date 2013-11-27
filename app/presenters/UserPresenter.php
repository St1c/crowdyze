<?php 

namespace App;

use Nette,
	Model\Services;


/**
 * Správa uživatele.
 */
class UserPresenter extends BasePresenter 
{
	/** 
	 * Informace o uživatelích.
	 * 
	 * @var Model\Services\UserService @inject 
	 */
	public $userService;
	
	
	/**
	 * Informace o taskcích.
	 *  
	 * @var Model\Services\TaskService @inject 
	 */
	public $taskService;
	
	
	/** @var Controls\IUserDetailsControlFactory @inject */
	public $userDetailsControlFactory;


	/** @var bool $edit */
	public $edit;


	/**
	 * Návrat z přihlášení.
	 */
	protected function startup()
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:', array( 'backlink' => $this->storeRequest() ));
		}
		else {
			$this->backlink = NULL;
		}
	}


	/**
	 * Default Action
	 */
	public function actionDefault()
	{
		$this->template->edit = $this->edit;
		$this->template->userData = $this->userService->getUserData($this->user->id);
		$this->template->balance 	= $this->userService->getBalance($this->user->id);
		$this->template->tasks		= $this->userService->getAcceptedUserTasks($this->user->id);
	}


	/**
	 * User details inline edit form
	 */
	protected function createComponentUserDetails()
	{
		return $this->userDetailsControlFactory->create();
	}


	/**
	 * Handle inline edit
	 */
	public function handleEdit()
	{
		$this->edit = TRUE;
		$this->template->edit = $this->edit;
		$this->validateControl();
		$this->invalidateControl('userProfile');
	}


	public function handleWorkerTasks()
	{
		$this->template->tasks = $this->userService->getAcceptedUserTasks($this->getUser()->id);

		if ($this->isAjax()) {
			$this->invalidateControl('tasks');			
		}
	}


	public function handleEmployerTasks()
	{
		$this->template->tasks = $this->taskService->getOwnerTasks($this->getUser()->id);
		if ($this->isAjax()) {
			$this->invalidateControl('tasks');			
		}
	}

}
