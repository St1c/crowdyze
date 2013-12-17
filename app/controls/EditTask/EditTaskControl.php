<?php 
namespace Controls;

use Nette,
	Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control,
	Nette\Application\Responses,
	Nette\Forms\Controls\SubmitButton,
	Nette\Utils\Strings,
	Nette\Utils\Validators,
	Nette\Image;
use Taco\Nette\Forms\Controls\DateInput,
	Taco\Nette\Forms\Controls\MultipleUploadControl,
	Taco\Nette\Http\FileUploaded;


class EditTaskControl extends BaseControl
{
	/** @var Model\Services\TaskService @inject */
	public $taskService;

	/** @var Model\Services\PayService @inject */
	public $payService;

	/** @var Model\Repositories\Budget_typeRepository @inject */
	public $budget_typeRepository;

	/** @var Model\Repositories\Department_nameRepository @inject */
	public $department_nameRepository;



	public function createComponentEditTaskForm($name)
	{
		$budgetTypes = $this->budget_typeRepository->getAll();
		$departments = $this->department_nameRepository->getAll($this->presenter->getUser()->id);
		$task = $this->taskService->getTaskByToken($this->presenter->getParameter('token'));
		$tags = array();
		foreach ($task->related('task_has_tag') as $tag) {
			if ($tag->tag->tag) {
				$tags[] = $tag->tag->tag;
			}
		}
		
		$attachments = array();
		foreach ($task->related('task_attachment') as $attachment) {
			$attachments[] = new FileUploaded($attachment->path);
		}

		$component = new Form($this, $name);
		$component->setTranslator($this->parent->translator);

		$component->addText('title', 'addTask.form.title')
			->setAttribute('placeholder', 'addTask.form.title')
			->addrule(Form::FILLED, 'addTask.form.title_missing')
			->setDefaultValue($task->title);

		$component->addTextArea('description', 'addTask.form.description')
			->setAttribute('placeholder', 'addTask.form.description')
			->AddRule(Form::FILLED, 'addTask.form.description_missing')
			->setDefaultValue($task->description);
		
		$component->addText('salary', 'addTask.form.salary')
			->setAttribute('placeholder', 'addTask.form.salary')
			->AddRule(Form::FILLED, 'addTask.form.salary_missing')			
			->setDefaultValue($task->salary);
		
		$component->addSelect('budget_type', 'addTask.form.budget_type')
			->setItems($budgetTypes, TRUE)
			->setDefaultValue($task->budgetType);

		$component->addText('tags', 'addTask.form.tags')
			->setAttribute('placeholder', 'addTask.form.tags')
			->setDefaultValue(self::formatTags($tags));
		
		$component->addText('workers', 'addTask.form.workers_required')
			->setAttribute('placeholder', 'addTask.form.workers_required')
			->setDefaultValue($task->workers);

		$component['deadline'] = new DateInput('Deadline:');
		$component['deadline']->setDefaultValue($task->deadline);
		
		$component->addText('departments', 'addTask.form.department')
			->setAttribute('placeholder', 'addTask.form.departments');

		$component['attachments'] = new MultipleUploadControl('attachments');
		$component['attachments']->setDefaultValue($attachments);
		$component->addSubmit('attachmentPreload', 'Preload')
				->setValidationScope(False);
		
		$component->addSubmit('submit', 'addTask.form.submit');
		$component->addSubmit('cancel', 'addTask.form.cancel')
				->setValidationScope(False);

		//~ $component->onError[] 	= $this->processError;
		$component->onSuccess[] = $this->processSubmitted;

		return $component;
	}



	public function processError(Form $component)
	{
		//~ if ($this->presenter->isAjax() ) {
			//~ $this->presenter->invalidateControl();
		//~ }
	}



	public function processSubmitted(Form $component)
	{
		if ($this->presenter->isAjax()) {
			$this->presenter->invalidateControl('task');
		}

		if ($component['cancel']->isSubmittedBy()) {
			$this->presenter->redirect('detail', array('token' => $this->presenter->getParameter('token')));
		}

		if ($component['attachmentPreload']->isSubmittedBy()) {}

		if ($component['submit']->isSubmittedBy()) {

			//	Reformat
			$values = $component->getValues();			
			foreach ($values as $key => $value) {
				//	Exclude tags, etc. from update
				$exclude = array('tags', 'departments', 'attachments', 'budget');
				in_array( $key,  $exclude ) || empty($value) ?: $update[$key] = $value;
			}

			//	Store
			$task = $this->taskService->getTaskByToken($this->presenter->getParameter('token'));
			$this->taskService->update($task, $update);

			// Saving tags
			if (isset($values['tags']) && $value = self::parseTags($values['tags'])) {
				$this->taskService->storeTags($task, $value);
			}

			// Saving departments 
			// if ( !empty($values['departments']) ) {
			// 	$this->taskService->setDepartments($task, $values['departments']);
			// }

			try {
				// Allocate money for the task from user's wallet
				$this->payService->updateBudget($task, $this->presenter->getUser()->id, $values);
				
				// Saving attachments
				foreach ($values->attachments as $file) {
					if ($file instanceof FileUploaded) {
						if ($file->isRemove()) {
							$this->taskService->removeAttachment($task, $file);
						}
						else {
							$this->taskService->saveAttachment($task, $file);
						}
					}
					else {
						throw new \LogicException('Invalid type of attachment.');
					}
				}
			} catch (\RuntimeException $e) {
				$component->addError($e->getMessage());
			}

			$this->presenter->flashMessage('addTask.flashes.task_edited', 'alert-success');
			$this->presenter->redirect('detail', array('token' => $this->presenter->getParameter('token')));
		}
	}



	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/EditTask.latte');
		$this->template->render();
	}



	/**
	 * @param string $value
	 * 
	 * @return array
	 */
	private static function parseTags($value)
	{
		return Strings::split($value, '~[,;]\s*~');
	}



	private static function formatTags(array $tags = array())
	{
		return implode(',', $tags);
	}

}
