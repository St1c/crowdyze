<?php 

namespace App;

use Nette,
	Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control,
	Nette\Application\Responses,
	Nette\Forms\Controls\SubmitButton,
	Nette\Utils\Strings,
	Nette\Utils\Validators,
	Nette\Image;
use Taco\Nette\Forms\Controls\MultipleUploadControl,
	Taco\Nette\Http\FileUploaded;


class ResultsPresenter extends BaseSignedPresenter 
{
	
	/** @var Model\Services\TaskService @inject */
	public $taskService;

	/** @var Model\Services\UserService @inject */
	public $userService;

	/** @var Model\Domains\Task */
	private $task;



	/**
	 * Add money to user wallet
	 */
	public function actionDefault($token)
	{ 
		// Get task info if exists
		$this->task = $this->redirectIfEmpty(
						$this->taskService->getTaskByToken(
							$this->redirectIfEmpty($token, $token)), 
						$token);

		// Check ownership
		if ($this->user->id !== $this->task->owner) {
			$this->flashMessage('notice.error.not_owner', 'alert-danger');
			$this->redirect('User:');
		}

		$this->template->results = $this->taskService->getResults($this->task);
	}


	/**
	 * Add new result
	 */
	public function actionAdd($token)
	{
		// Get task info if exists
		$this->task = $this->redirectIfEmpty(
						$this->taskService->getTaskByToken(
							$this->redirectIfEmpty($token, $token)), 
						$token);

		// Check if user is assigned to this task
		if (!$this->userService->isAcceptedFilterByStatus($this->task->id, $this->user->id)) {
			$this->flashMessage('notice.error.not_accepted', 'alert-danger');
			$this->redirect('User:');
		};
	}


	/**
	 * Create Result Form
	 */
	protected function createComponentResultForm($name)
	{
		$component = new Form($this, $name);
		$component->setTranslator($this->translator);
		
		$component->addTextArea('result', 'result.form.result')
			->setAttribute('placeholder', 'result.form.result')
			->AddRule(Form::FILLED, 'result.form.result_missing');

		$component['attachments'] = new MultipleUploadControl('attachments');
		$component->addSubmit('attachmentPreload', 'Preload')
				->setValidationScope(FALSE);
		
		$component->addSubmit('submit', 'result.form.submit');
		$component->addSubmit('cancel', 'result.form.cancel')
				->setValidationScope(FALSE);
				
		//~ $component->onError[] 	= $this->processError;
		$component->onSuccess[] = $this->processSubmitted;

		return $component;
	}


	/**
	 * Process submitted result form
	 * @param  Form   $component [description]
	 */
	public function processSubmitted(Form $component)
	{
		if ($this->isAjax()) {
			$this->invalidateControl('task');
		}

		if ($component['cancel']->isSubmittedBy()) {
			$this->redirect('User:');
		}

		if ($component['attachmentPreload']->isSubmittedBy()) {
		}

		if ($component['submit']->isSubmittedBy()) {

			$values = $component->getValues();

			//	Store new result
			try {
				$result = $this->taskService->createResult($this->user->id, $this->task->id, (array)$values);
			}
			catch (\Exception $e) {
				$component->addError($e->getMessage());
				return;
			}

			$this->redirect('User:');
		}
	}



	public function handleAccept($userId)
	{
		// Check if user is assigned to this task, status = 2|Pending
		if (!$this->userService->isPendingFilterByStatus($this->task->id, $userId)) {
			$this->flashMessage('notice.error.not_assigned_to_user', 'alert-danger');
			$this->redirect('User:');
		};

		// Accept result
		try {
			$this->taskService->doAcceptResult($this->task, (int) $userId);
		}
		catch (\RuntimeException $e) {
			$this->flashMessage($e->getMessage(), 'alert-danger');
			$this->redirect('User:');
		}
		$this->redirect('this');
	}



	public function handleReject($userId)
	{
		try {
			$this->taskService->doRejectResult($this->task->id, (int) $userId);
		}
		catch (\RuntimeException $e) {
			$this->flashMessage($e->getMessage(), 'alert-danger');
		}
			
		$this->redirect('this');
	}



	/**
	 * Asserting $value by empty and if is empty, than redirect to default action of presenter.
	 * 
	 * @param $value Asserted value.
	 * @param $token Added value for message.
	 */
	private function redirectIfEmpty($value, $token)
	{
		if (!$value) {
			$this->flashMessage("notice.error.task_not_found", 'alert-danger', NULL, array('token' => $token));
			$this->redirect('default');
		}
		return $value;
	}

}
