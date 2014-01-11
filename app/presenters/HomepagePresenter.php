<?php

namespace App;

use Nette,
	Nette\Latte\MacroNode,
	Nette\Latte\PhpWriter;
use Model,
	Controls;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BaseSignedPresenter
{
	/** @var Nette\Mail\IMailer @inject */
	public $mailer;

	/** @var Model\Services\TaskService @inject */
	public $taskService;

	/** @var Model\Services\UserService @inject */
	public $userService;



}
