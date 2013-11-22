<?php 

namespace App;

use Nette,
	Model\Repositories\UsersRepository;

class MessagesPresenter extends BasePresenter 
{
	/** @var Model\Repositories\UsersRepository @inject */
	public $usersRepository;

	protected function startup()
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:', array( 'backlink' => $this->storeRequest() ));
		} else {
			$this->backlink = NULL;
		}
	}

	public function actionDefault()
	{
	}
}