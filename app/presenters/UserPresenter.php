<?php 

namespace App;

use Nette,
	Model\Services\UsersRepository;

class UserPresenter extends BasePresenter 
{
	/** @var Model\Services\UsersService @inject */
	public $usersService;

	/** @var Controls\IUserDetailsControlFactory @inject */
	public $userDetailsControlFactory;

	/** @var bool $edit */
	public $edit;

	protected function startup()
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Signup:', array( 'backlink' => $this->storeRequest() ));
		} else {
			$this->backlink = NULL;
		}
	}

	/**
	 * Default Action
	 */
	public function actionDefault()
	{
		$this->template->edit 		= $this->edit;
		$this->template->userData 	= $this->usersService->getUserData($this->getUser()->id);
		$this->template->tasks 		= $this->usersService->getAcceptedUserTasks($this->getUser()->id);
	}

	protected function createComponentUserDetails()
	{
		return $this->userDetailsControlFactory->create();
	}

	public function handleEdit()
	{
		$this->edit = TRUE;
		$this->template->edit = $this->edit;
		$this->validateControl();
		$this->invalidateControl('userProfile');
	}
}