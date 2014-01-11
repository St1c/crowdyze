<?php 

namespace App;

use Nette,
	Model\Services\UserService;

class MessagesPresenter extends BaseSignedPresenter 
{
	/** @var Model\Services\UserService @inject */
	public $userService;


}
