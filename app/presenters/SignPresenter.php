<?php
namespace App;

use Nette,
	Nette\Latte\MacroNode,
	Nette\Latte\PhpWriter;
use Model,
	Controls;


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

	public function actionDefault()
	{

		if ($this->isAjax()) {
			$this->invalidateControl('registration');
		}

	}

	public function actionRegister()
	{

		if ($this->isAjax()) {
			$this->invalidateControl('registration');
		}

	}
}
