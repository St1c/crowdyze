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
	
	
	/**
	 * @TODO
	 * @var Controls\IUserDetailsControlFactory @inject
	 */
	public $userDetailsControlFactory;


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
	 * @param string $filter worker | employer
	 */
	public function renderDefault($filter = Null)
	{
		if (! $this->template->userData = $this->userService->getUserData($this->user->id)) {
			$this->error('User is not found.');
		}
		
		switch ($filter) {
			case 'employer':
				$this->template->tasks = $this->taskService->getOwnerTasks($this->user->id);
				break;
			case 'worker':
				$this->template->tasks = $this->userService->getAcceptedUserTasks($this->user->id);
				break;
			default:
				$this->redirect('this', array( 'filter' => 'worker' ));
		}

		$this->template->balance = $this->userService->getBalance($this->user->id);
	}



	/**
	 * Edit of user data.
	 */
	public function renderEdit()
	{
		$this->template->userData = $this->userService->getUserData($this->user->id);
		$this->template->balance = $this->userService->getBalance($this->user->id);
		$this->template->tasks = $this->userService->getAcceptedUserTasks($this->user->id);
		//~ $this->validateControl();
		//~ $this->invalidateControl('userProfile');
	}



	/**
	 * User details inline edit form
	 */
	protected function createComponentUserDetails()
	{
		return $this->userDetailsControlFactory->create();
	}

}
