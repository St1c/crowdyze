<?php

namespace App;

use Nette,
	Model,
	Controls;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
	/** @var Controls\IAddTaskControlFactory @inject */
	public $addTaskControlFactory;
	/** @var Nette\Mail\IMailer @inject */
	public $mailer;

	protected function createComponentAddTask()
	{
		return $this->addTaskControlFactory->create();
	} 

	protected function createComponentForm()
	{
		$form = new Nette\Application\UI\Form;
		$form->setTranslator($this->translator);

		$form->addText('from')
			->setAttribute('class', 'form-control')
			->setAttribute('placeholder', 'email');
		$form->addTextarea('text')
			->setAttribute('class', 'form-control')
			->setAttribute('placeholder', 'Message');
		$form->addSubmit('send', 'Send')
			->setAttribute('class', 'btn btn-primary');

		$form->onSuccess[] = $this->sendEmails;
		return $form;
	}

	public function sendEmails($form)
	{
		$message 	= $form->values->text;
		$email 		= $form->values->from;
		
		
		$mail = new Nette\Mail\Message;
		$mail->addTo($email)
			->setBody($message);

		$this->mailer->send($mail);
		
	}

}
