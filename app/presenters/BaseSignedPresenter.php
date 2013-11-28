<?php

namespace App;

use Nette,
	Model,
	Controls,
	Kdyby\Translation\Translator;


/**
 * Base presenter for all application presenters.
 */
abstract class BaseSignedPresenter extends BasePresenter
{

	/** @persistent */
	public $backlink;



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
	 * Sign out signal
	 */
	public function handleSignOut()
	{
		$this->getUser()->logout(TRUE);
		$this->flashMessage('Successfully signed out!', 'alert-success');
		$this->redirect('Sign:');
	}


}
