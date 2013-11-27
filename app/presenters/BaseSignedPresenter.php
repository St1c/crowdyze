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
	/** @var \Kdyby\Translation\Translator @inject */
	public $translator;


	/** @var \Kdyby\Translation\LocaleResolver\SessionResolver @inject */
	public $translatorSession;


	/** @persistent */
	public $backlink;


	/** @var Controls\IAddTaskControlFactory @inject */
	public $addTaskControlFactory;


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


	/**
	 * Handle singal for language change
	 * @FIXME Zrušit ukládání jazyka do session.
	 * 
	 * @param  string $locale 
	 */
	public function handleChangeLocale($locale)
	{
		$this->translatorSession->setLocale($locale);
		$this->redirect('this');
	}


	/**
	 * Add Task From control factory
	 * 
	 * @return 	\Nette\Application\UI\Control AddTaskControl
	 */
	protected function createComponentAddTask()
	{
		return $this->addTaskControlFactory->create();
	}


	/**
	 * Add translator to templates
	 * 
	 * @param  {string|NULL} $class 
	 * @return Template
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->registerHelperLoader(callback($this->translator->createTemplateHelpers(), 'loader'));

		// Register new helper
		//	@TODO Samostatná funkce
		$template->registerHelper('daysLeft', function ($deadline) {
			
			if (!$deadline) {
				return '';
			}
			
			//Calculate difference
			$seconds = strtotime($deadline) - time(); 	//time returns current time in seconds
			if ($seconds < 0 ) return '';

			$days 		= floor($seconds / 86400);
			$seconds 	%= 86400;

			$hours 		= floor($seconds / 3600);
			$seconds 	%= 3600;

			$minutes 	= floor($seconds / 60);
			$seconds 	%= 60;

			if ($days >= 1) {
				return "$days days left";
			}
			
			if ($hours >= 1) {
				return "$hours hours left";
			}
			else {
				return "$minutes minutes left";
			}

		});

		return $template;
	}



	/**
	 * Translate Flash Messages
	 * 
	 * @param  string $message    Flash Message content
	 * @param  string $type       Message type
	 * @param  int    $count      Count for plural nouns
	 * @param  array  $parameters Message paramaters
	 * @return Flash              Flash message - translated
	 */
	public function flashMessage($message, $type = "info", $count = NULL, array $parameters = array())
	{
		$message = $this->translator->translate($message, $count, $parameters);
		return parent::flashMessage($message, $type);
	}

}