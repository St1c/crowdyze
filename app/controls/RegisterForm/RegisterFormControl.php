<?php
namespace Controls;

use Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control;

class RegisterFormControl extends BaseControl
{

	/** @var Model\Services\UsersService @inject */
	public $usersService;
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
			->addrule(Form::FILLED, 'login.form.email_warning');
		$register->addPassword('password', 'login.form.password')
			->setAttribute('placeholder', 'login.form.password')
			->AddRule(Form::MIN_LENGTH, $register->translator->translate('login.registerForm.password_length', NULL, array('length' => 6)));
		$register->addPassword('repeatPassword', 'login.registerForm.password_repeat')
			->setAttribute('placeholder', 'login.registerForm.password_repeat')
			->AddRule(Form::FILLED, 'login.registerForm.password_repeat_warning')
			->AddRule(Form::EQUAL, 'login.registerForm.password_match', $register['password']);
		$register->addSubmit('submit', 'login.registerForm.sign_up');

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
			$this->usersService->register('email', $values );

			$this->presenter->getUser()->setExpiration('+15 days', FALSE);
			$this->presenter->getUser()->login('email', $values);		
			
			$this->presenter->flashMessage('login.flashes.register_success.', 'alert-success');
			$this->presenter->restoreRequest($this->presenter->backlink);
			$this->presenter->redirect('Overview:');

		} catch (\Nette\Security\AuthenticationException $e) {
			$register->addError($e->getMessage(), 'alert-error');
		}

	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/RegisterForm.latte');
		$this->template->render();
	}
}