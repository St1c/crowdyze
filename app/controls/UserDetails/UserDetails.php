<?php 
namespace Controls;

use Nette,
	Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control,
	Nette\Utils\Strings,
	Nette\Image;


/**
 * Uprava detailu uživatele.
 */
class UserDetailsControl extends BaseControl
{
	/** @var Model\Services\UserService @inject */
	public $userService;



	public function createComponentUserDetailsForm()
	{
		$user = $this->userService->getUserData($this->presenter->getUser()->id);

		$userDetailsForm = new Form();

		$userDetailsForm->setTranslator($this->parent->translator);

		$userDetailsForm->addText('first_name', 'userProfile.form.first_name')
			->setAttribute('placeholder', 'userProfile.form.first_name')
			->setDefaultValue($user['first_name']);

		$userDetailsForm->addText('last_name', 'userProfile.form.last_name')
			->setAttribute('placeholder', 'userProfile.form.last_name')
			->setDefaultValue($user['last_name']);
		
		$userDetailsForm->addText('city', 'userProfile.form.city')
			->setAttribute('placeholder', 'userProfile.form.city')
			->setDefaultValue($user['city']);
		
		$userDetailsForm->addText('country', 'userProfile.form.country')
			->setAttribute('placeholder', 'userProfile.form.country')
			->setDefaultValue($user['country']);

		$userDetailsForm->addSubmit('submit', 'userProfile.form.submit');
		$userDetailsForm->addSubmit('cancel', 'userProfile.form.cancel');

		$userDetailsForm->onError[] = $this->processError;
		$userDetailsForm->onSubmit[] = $this->processSubmitted;

		return $userDetailsForm;
	}



	/**
	 * Zpracování chyby.
	 * 
	 * @param Form $form Který formulář zpracováváme.
	 */
	public function processError(Form $form)
	{
		die('error');
		if ($this->isAjax() ) {
			$this->invalidateControl();
		}
	}



	/**
	 * Zpracování korektních dat.
	 * 
	 * @param Form $form Který formulář zpracováváme.
	 */
	public function processSubmitted(Form $form)
	{
		$values = $form->getValues();
		
		if ($form['cancel']->isSubmittedBy()) {
			//~ if ($this->presenter->isAjax()) {
				//~ $this->presenter->invalidateControl('userProfile');
			//~ }
		}
		
		if ($form['submit']->isSubmittedBy()) {
			foreach ($values as $key => $value) {
				empty($value) ?: $update[$key] = $value;
			}
			$userData = $this->userService->getUserData($this->presenter->getUser()->id);
			$this->userService->update($userData, $update);

			$this->presenter->flashMessage('userProfile.flashes.profile_edited', 'alert-success');
			//~ $this->presenter->template->userData = $userData;
		}

		//~ $this->presenter->edit = FALSE;
		//~ $this->presenter->template->edit = $this->presenter->edit;

		//~ if ($this->presenter->isAjax()) {
			//~ $this->presenter->invalidateControl('userProfile');
		//~ }
		
		$this->presenter->redirect('default');
	}



	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/UserDetailsForm.latte');
		$this->template->render();
	}

}
