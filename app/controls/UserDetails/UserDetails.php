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

		$userDetailsForm->addUpload('profile_photo', 'Profile photo');

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
		$userDetailsForm->addSubmit('cancel', 'userProfile.form.cancel')
				->setValidationScope(False);

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
		if ($form['cancel']->isSubmittedBy()) {
		}
		
		if ($form['submit']->isSubmittedBy()) {
			$values = $form->getValues();
			foreach ($values as $key => $value) {
				empty($value) ?: $update[$key] = $value;
			}
			
			if (! $values->profile_photo->isOk()) {
				$component->addError('Nepodařilo se načíst soubor: ' . $values->profile_photo->error);
				return;
			}
			
			$entry = $this->userService->getUserData($this->presenter->getUser()->id);

			try {
				$this->userService->update($entry, $update);
				$this->presenter->flashMessage('userProfile.flashes.profile_edited', 'alert-success');
			}
			catch (\RuntimeException $e) {
				$component->addError($e->getMessage());
				return;
			}
		}

		$this->presenter->redirect('default');
	}



	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/UserDetailsForm.latte');
		$this->template->render();
	}

}
