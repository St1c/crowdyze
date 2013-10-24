<?php

namespace App;

use Nette,
	Model,
	Kdyby\Translation\Translator;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @persistent */
	public $locale;
	/** @var \Kdyby\Translation\Translator */
	public $translator;
	/** @persistent */
	public $backlink;

	/**
	 * Set new translator
	 * 
	 * @param \Kdyby\Translation\Translator
	 */
	public function injectTranslator(Translator $translator)
	{
		$this->translator = $translator;
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
		$template->registerHelper('daysLeft', function ($deadline) {
			
			if (!$deadline) return '';
			//Calculate difference
			$seconds 	= strtotime($deadline) - time(); 	//time returns current time in seconds
			if ($seconds < 0 ) return '';

			$days 		= floor($seconds / 86400);
			$seconds 	%= 86400;

			$hours 		= floor($seconds / 3600);
			$seconds 	%= 3600;

			$minutes 	= floor($seconds / 60);
			$seconds 	%= 60;

			if ($days >= 1) return "$days days left";
			if ($hours >= 1) return "$hours hours left";
			else return "$minutes minutes left";

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

	/**
	 * Sign out signal
	 */
	public function handleSignOut()
	{
		$this->getUser()->logout(TRUE);
		$this->flashMessage('Successfully signed out!', 'alert-success');
		$this->redirect('Homepage:');
	}

}
