<?php
namespace Controls;


use Nette,
	Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control,
	Model\Domains,
	Model\Services;


/**
 * Form plus List of comments.
 */
class DiscussFormControl extends BaseControl
{

	private $task;


	private $user;


	private $discussService;


	/**
	 * DI
	 */
	public function __construct(
			Services\DiscussService $discussService
			)
	{
		$this->discussService = $discussService;
	}
	
	
	public function setValue(Domains\Task $task)
	{
		$this->task = $task;
		return $this;
	}



	public function setUser(Nette\Security\User $user)
	{
		$this->user = Domains\User::createFromArray($user->identity->data);
		return $this;
	}



	public function getTask()
	{
		return $this->task;
	}


	/**
	 * Discuss in Form
	 */
	public function createComponentDiscussForm()
	{
		$form = new Form();
		$form->setTranslator($this->parent->translator);

		$form->addTextarea('content', 'discuss.form.content')
			->AddRule(Form::FILLED, 'discuss.form.content-warning');
		$form->addSubmit('submit', 'discuss.form.send');

		$form->onSuccess[] = $this->processSubmitted;

		return $form;
	}



	/**
	 * Discuss in Form processing
	 */
	public function processSubmitted(Form $component)
	{
		if ($component['submit']->isSubmittedBy()) {

			// Reformat
			$values = Domains\Discuss::createFromArray($component->getValues(True));
			$values->task = $this->task;
			$values->author = $this->user;

			//	Process store
			try {
				$task = $this->discussService->createComment($values);
				$this->presenter->flashMessage('discuss.flashes.comment-added', 'alert-success');
			}
			catch (\RuntimeException $e) {
				$component->addError(
					$this->parent->translator->translate($e->getMessage())
				);
				return;
			}
			$this->presenter->redirect('this');
			//~ $this->presenter->redirect('this', array('token' => $task->token));
		}
	}



	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/DiscussForm.latte');
		$this->template->render();
	}

}
