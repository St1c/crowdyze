<?php
namespace Controls;

use Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control,
	VojtechDobes\MultiAuthenticator;

class LoginFormControl extends BaseControl
{

	/**
	 * Sign in Form
	 */
	public function createComponentSignInForm()
	{
		$signIn = new Form();
		$signIn->setTranslator($this->parent->translator);

		$signIn->addText('email', 'login.form.email')
			->setAttribute('placeholder', 'login.form.email')
			->addrule(Form::FILLED, 'login.form.email-warning');
		$signIn->addPassword('password', 'login.form.password')
			->setAttribute('placeholder', 'login.form.password')
			->AddRule(Form::FILLED, 'login.form.password-warning');
		$signIn->addSubmit('submit', 'login.form.sign-in');

		$signIn->onSuccess[] = $this->signInFormSubmitted;

		return $signIn;
	}

	/**
	 * Sign in Form processing
	 */
	public function signInFormSubmitted(Form $signIn)
	{
		$values = $signIn->getValues(TRUE);

		try {
			$this->presenter->getUser()->setExpiration('+15 days', FALSE);
			$this->presenter->getUser()->login('email', $values);						
			// Authentication successful, login in!
			$this->presenter->flashMessage('login.flashes.login-success', 'alert-success');
			// $this->presenter->restoreRequest($this->presenter->backlink);
			$this->presenter->redirect(':Task:');

		} catch (\Nette\Security\AuthenticationException $e) {
			$this->presenter->template->register = false;
			$signIn->addError($e->getMessage(), 'alert-error');
		}

	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/LoginForm.latte');
		$this->template->render();
	}
}