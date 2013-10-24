<?php
namespace App;

use Nette,
	Controls,
	VojtechDobes\MultiAuthenticator;

/**
 * Sign in/out presenters.
 */
class SignupPresenter extends BasePresenter
{

	/** @var Controls\IRegisterFormControlFactory @inject */
	public $registerFormControlFactory;	
	/** @var Controls\ILoginFormControlFactory @inject */
	public $loginFormControlFactory;
	/** @var Controls\ISocialLoginControlFactory @inject */
	public $socialLoginControlFactory;

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
}
