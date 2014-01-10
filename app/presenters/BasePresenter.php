<?php

namespace App;


use Nette,
	Nette\Latte\Engine;
use	Model,
	Controls;
use	Kdyby\Translation\Translator;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	const ITEMS_PER_PAGE = 24;


	/** @var \Kdyby\Translation\Translator @inject */
	public $translator;


	/** @var \Kdyby\Translation\LocaleResolver\SessionResolver @inject */
	public $translatorSession;



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
	 * Add translator to templates
	 * 
	 * @param  {string|NULL} $class 
	 * @return Template
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->registerHelperLoader(callback($this->translator->createTemplateHelpers(), 'loader'));
		$template->registerHelperLoader(callback('App\Helpers', 'loader'));
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
