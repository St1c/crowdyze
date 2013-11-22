<?php
namespace Utilities;

use Nette,
	Nette\Mail;

class MailerService
{
	/** @var Nette\Mail\Mailer */
	private $mailer;

	public function __construct(Mail\IMailer $mailer)
	{
		$this->mailer = $mailer;
	}

	public function sendWelcomeMail($address)
	{

		// Setup a template and pass variables
		$template = new Nette\Templating\FileTemplate( __DIR__ . '/../../templates/Mails/Register.latte');
		$template->registerFilter(new Nette\Latte\Engine);
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');
		$template->id = 123;

		// Create new Mail message
		$mail = new Mail\Message;

		$mail ->setFrom('info@dataworkers.eu')
				->setSubject('Welcome to Crowdyze.me')
				->addTo($address)
				->setHtmlBody($template);

		// Send Mail
		$this->mailer->send($mail);
	}


	public function sendAfterSurveyMail($address)
	{

		// Setup a template and pass variables
		$template = new Nette\Templating\FileTemplate( __DIR__ . '/../../templates/Mails/SurveyInvite.latte');
		$template->registerFilter(new Nette\Latte\Engine);
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');
		// $template->id = 123;

		// Create new Mail message
		$mail = new Mail\Message;

		$mail ->setFrom('info@dataworkers.eu')
				->setSubject('Thank you for your opinion')
				->addTo($address)
				->setHtmlBody($template);

		// Send Mail
		$this->mailer->send($mail);
	}
}

?>