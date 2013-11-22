<?php

namespace App;

use Nette,
	Model,
	Controls,
	Nette\Application\UI\Form;


/**
 * Homepage presenter.
 */
class SurveyPresenter extends BasePresenter
{
	/** @var \Utilities\MailerService @inject */
	public $mailerService;
	/** @var Model\Services\PollService @inject */
	public $pollService;


	protected function createComponentSurveyForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);

		$form->addRadioList('question1', 'survey.form.quest1.quest', array(
								$this->translator->translate('survey.form.quest1.ans1'), 
								$this->translator->translate('survey.form.quest1.ans2')
							))
			->setRequired('survey.form.quest1.req');

		$form->addRadioList('question2', 'survey.form.quest2.quest', array(
								$this->translator->translate('survey.form.quest2.ans1'), 
								$this->translator->translate('survey.form.quest2.ans2')
							))
			->setRequired('survey.form.quest2.req');

		$form->addRadioList('question3', 'survey.form.quest3.quest', array(
								$this->translator->translate('survey.form.quest3.ans1'),
								$this->translator->translate('survey.form.quest3.ans2')
							))
			->setRequired('survey.form.quest3.req');

		$form->addRadioList('question4', 'survey.form.quest4.quest', array(
								$this->translator->translate('survey.form.quest4.ans1'), 
								$this->translator->translate('survey.form.quest4.ans2'),
								$this->translator->translate('survey.form.quest4.ans3'),
								$this->translator->translate('survey.form.quest4.ans4')
							))
			->setRequired('survey.form.quest4.req');

		$form->addRadioList('question5', 'survey.form.quest5.quest', array(
								$this->translator->translate('survey.form.quest5.ans1'), 
								$this->translator->translate('survey.form.quest5.ans2'),
								$this->translator->translate('survey.form.quest5.ans3'),
								$this->translator->translate('survey.form.quest5.ans4'),
								$this->translator->translate('survey.form.quest5.ans5'),
								$this->translator->translate('survey.form.quest5.ans6'),
								$this->translator->translate('survey.form.quest5.ans7')
							))
			->setRequired('survey.form.quest5.req');

		$form->addRadioList('question6', 'survey.form.quest6.quest', array(
								$this->translator->translate('survey.form.quest6.ans1'), 
								$this->translator->translate('survey.form.quest6.ans2'),
								$this->translator->translate('survey.form.quest6.ans3'),
								$this->translator->translate('survey.form.quest6.ans4')
							))
			->setRequired('survey.form.quest6.req');

		$form->addSubmit('submit', 'survey.form.submit')
			->setAttribute('class', 'btn btn-default');

		$form->onSuccess[] = $this->surveyFormSubmitted;
		return $form;
	}


	public function surveyFormSubmitted(Form $form)
	{
		$values = $form->getValues();
		try {
			$poll = $this->pollService->createNewPoll();

			foreach ($values as $tag => $value) {	
				$this->pollService->addAnswer($poll, $tag, $value);
			}

		} catch (\Exception $e) {
			$this->flashMessage($e->getMessage(), 'alert-danger');
			$this->redirect('this');
		}

		// $this->flashMessage('Successfully recorded', 'alert-success');
		$this->redirect('Survey:subscribe', $poll->uuid);
	}


	protected function createComponentSubscribeForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);

		$form->addtext('email', 'survey.form.subscribe.email')
				->setAttribute('placeholder', 'survey.form.subscribe.email_required');

		$form->addCheckbox('subscribe', 'survey.form.subscribe.subscribe');

		$form->addSubmit('submit', 'survey.form.submit')
			->setAttribute('class', 'btn btn-default');

		$form->onSuccess[] = $this->subscribeFormSubmitted;
		return $form;
	}

	public function subscribeFormSubmitted(Form $form)
	{
		$values = $form->getValues();
		$poll = $this->presenter->getParameter('poll');

		try {
			if (isset($values->email)) {
				$this->pollService->assignEmailToPoll($poll, $values);			
			}
		} catch (\Exception $e) {
			$this->flashMessage($e->getMessage(), 'alert-danger');
			$this->redirect('this');
		}

		// $this->flashMessage('Successfully recorded', 'alert-success');
		$this->redirect('Survey:thanks');
	}

	public function actionSubscribe($poll){}


	private function sendEmails($email)
	{
		$this->mailerService->sendAfterSurveyMail($email);		
	}
}
