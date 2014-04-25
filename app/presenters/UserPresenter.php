<?php 

namespace App;


use Nette,
	Nette\Application\BadRequestException,
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
	public function actionDefault($filter = 'worker')
	{
		$this->template->userData = $this->assertEmpty($this->userService->getUserData($this->user->id), 'User is not found.');
		$this->template->ownerTasksCount = $this->taskService->getOwnerTasksCount($this->user->id);
		$this->template->activeJobs = $this->userService->getAcceptedUserTasksCount($this->user->id);

		$tasks = NULL;
		switch ($filter) {
			case 'employer':
				$tasks = $this->taskService->getOwnerTasks($this->user->id);
				break;
			case 'worker':
				$tasks = $this->userService->getAcceptedUserTasks($this->user->id);
				$this->template->finishedTasks = $this->userService->getFinishedUserTasks($this->user->id);
				break;
			default:
				$this->redirect('this', array( 'filter' => 'worker' ));
		}

		$this->template->balance = $this->payService->getWallet($this->user->id);
		$this->template->tasks = $tasks;
	}



	/**
	 * Detail of user.
	 */
	public function renderDetail($token)
	{
		$this->template->userData = $this->assertEmpty($this->userService->getUserDataByToken($token));
	}



	/**
	 * Edit of user data.
	 */
	public function renderEdit()
	{
		$this->template->userData = $this->userService->getUserData($this->user->id);
		$this->template->balance = $this->payService->getWallet($this->user->id);
		$this->template->tasks = $this->userService->getAcceptedUserTasks($this->user->id);

		$this->template->activeJobs = $this->userService->getAcceptedUserTasksCount($this->user->id);
		$this->template->ownerTasksCount = $this->taskService->getOwnerTasksCount($this->user->id);
		$this->template->activeJobs = $this->userService->getAcceptedUserTasksCount($this->user->id);
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



	/**
	 * Validate for exist.
	 */
	private function assertEmpty($value, $label = 'Record is not found.')
	{
		if (empty($value)) {
			$this->error($label);
		}
		return $value;
	}

}
