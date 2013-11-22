<?php
namespace App;

use Nette,
	Controls,
	VojtechDobes\MultiAuthenticator;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

	/** @var Controls\IRegisterFormControlFactory @inject */
	public $registerFormControlFactory;	
	/** @var Controls\ILoginFormControlFactory @inject */
	public $loginFormControlFactory;
	/** @var Controls\ISocialLoginControlFactory @inject */
	public $socialLoginControlFactory;

	public $register;

	public function startup()
	{
		parent::startup();
		$this->template->register = TRUE;
	}

	public function createComponentRegisterForm()
	{
		return $this->registerFormControlFactory->create();
	}

	public function createComponentLoginForm()
	{
		return $this->loginFormControlFactory->create();
	}

	public function createComponentSocialLogin()
	{
		return $this->socialLoginControlFactory->create();
	}

	public function handleRegister($type = 'register')
	{
		if ($type == 'register') {
			$this->template->register = true;			
		} else {
			$this->template->register = false;
		}

		if ($this->isAjax()) {
			$this->invalidateControl('registration');
		}

	}
}
