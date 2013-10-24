<?php
namespace Model\Authenticators;

use Nette,
	Model\Repositories,
	Model\Services,
	Utilities\MailerService;

class BaseAuthenticator
{

	/** @var UsersRepository */
	protected $usersRepository;
	/** @var UsersService */
	protected $usersService;


	public function __construct(Services\UsersService $usersService)
	{
		$this->usersService = $usersService;
	}

}
