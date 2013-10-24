<?php 
namespace Controls;

use Nette\Application\UI\Control;

abstract class BaseControl extends Control
{

	/**
	 * Redefine contrunt method to add translator
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->registerHelperLoader(callback($this->parent->translator->createTemplateHelpers(), 'loader'));

		return $template;
	}

}