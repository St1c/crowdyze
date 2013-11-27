<?php 

namespace App;

use Nette,
	Model\Repositories\UsersRepository;

class MessagesPresenter extends BaseSignedPresenter 
{
	/** @var Model\Repositories\UsersRepository @inject */
	public $usersRepository;


}
