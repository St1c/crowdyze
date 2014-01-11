<?php 

namespace App;


use Nette,
	Model\Services,
	Model\Domains;


/**
 * Správa uživatele.
 */
class UserPresenter extends BaseSignedPresenter 
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
	 * Informace o taskcích.
	 *  
	 * @var Model\Services\PayService @inject 
	 */
	public $payService;

	/**
	 * @TODO
	 * @var Controls\IUserDetailsControlFactory @inject
	 */
	public $userDetailsControlFactory;



	/**
	 * Default Action
	 * @param string $filter worker | employer
	 */
	public function renderDefault($filter = 'worker')
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

		$this->template->balance = $this->payService->getWallet($this->user->id);
	}



	/**
	 * Edit of user data.
	 */
	public function renderEdit()
	{
		$this->template->userData = $this->userService->getUserData($this->user->id);
		$this->template->balance = $this->payService->getWallet($this->user->id);
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
