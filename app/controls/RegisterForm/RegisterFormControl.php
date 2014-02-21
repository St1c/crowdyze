<?php
namespace Controls;

use Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control;

class RegisterFormControl extends BaseControl
{

	/** @var Model\Services\SignService @inject */
	public $signService;
	/** @var Utilities\MailerService @inject */
	public $mailerService;

	/**
	 * Register Form
	 */
	public function createComponentRegisterForm()
	{
		$register = new Form();
		$register->setTranslator($this->parent->translator);

		$register->addText('email', 'login.form.email')
			->setAttribute('placeholder', 'login.form.email')
			->addrule(Form::FILLED, 'login.form.email-warning');
		$register->addPassword('password', 'login.form.password')
			->setAttribute('placeholder', 'login.form.password')
			->AddRule(Form::MIN_LENGTH, $register->translator->translate('login.registerForm.password-length', NULL, array('length' => 6)), 6);
		$register->addPassword('repeatPassword', 'login.registerForm.password-repeat')
			->setAttribute('placeholder', 'login.registerForm.password-repeat')
			->AddRule(Form::FILLED, 'login.registerForm.password_repeat-warning')
			->AddRule(Form::EQUAL, 'login.registerForm.password-match', $register['password']);
		$register->addSubmit('submit', 'login.registerForm.sign-up');

		$register->onSuccess[] = $this->registerFormSubmitted;

		return $register;
	}


	/**
	 * Register Form processing
	 */
	public function registerFormSubmitted(Form $register)
	{
		$values = $register->getValues(TRUE);

		try {
			// Try to register non-existing user
			$this->signService->register('email', $values );

			$this->presenter->getUser()->setExpiration('+15 days', FALSE);
			$this->presenter->getUser()->login('email', $values);		
			
			$this->presenter->flashMessage('login.flashes.register-success.', 'alert-success');
			// $this->presenter->restoreRequest($this->presenter->backlink);
		} 
		catch (\Nette\Security\AuthenticationException $e) {
			$register->addError($e->getMessage(), 'alert-error');
			return ;
		}

		$this->presenter->redirect(':Task:');
	}



	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/RegisterForm.latte');
		$this->template->render();
	}
}
