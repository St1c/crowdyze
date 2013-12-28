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
	Taco\Nette\Forms\Controls\MultipleUploadControl;
use DateTime,
	DateInterval;


class AddTaskControl extends BaseControl
{
	/** @var Model\Services\TaskService @inject */
	public $taskService;

	/** @var Model\Repositories\Budget_typeRepository @inject */
	public $budget_typeRepository;

	/** @var Model\Repositories\Department_nameRepository @inject */
	public $department_nameRepository;



	public function createComponentAddTaskForm($name)
	{
		$budgetTypes = $this->budget_typeRepository->getAll();
		$departments = $this->department_nameRepository->getAll($this->presenter->getUser()->id);

		$component = new Form($this, $name);
		$component->setTranslator($this->parent->translator);

		$component->addText('title', 'addTask.form.title')
			->setAttribute('placeholder', 'addTask.form.title')
			->addrule(Form::FILLED, 'addTask.form.title_missing');
		
		$component->addTextArea('description', 'addTask.form.description')
			->setAttribute('placeholder', 'addTask.form.description')
			->AddRule(Form::FILLED, 'addTask.form.description_missing');
		
		$component->addText('salary', 'addTask.form.salary')
			->setAttribute('placeholder', 'addTask.form.salary')
			->setAttribute('size', 6)
			->AddRule(Form::FILLED, 'addTask.form.salary_missing');
		
		$component->addSelect('budget_type', 'addTask.form.budget_type')
			->setItems($budgetTypes, TRUE)
			->setDefaultValue(3);

		$component->addText('tags', 'addTask.form.tags')
			->setAttribute('placeholder', 'addTask.form.tags');
		
		$component->addText('workers', 'addTask.form.workers_required')
			->setDefaultValue(1)
			->setAttribute('placeholder', 'addTask.form.workers_required');
		
		$component['deadline'] = new DateInput('Deadline:');
		$component['deadline']->setDefaultValue((new DateTime())->add(new DateInterval('P1M')));
		
		$component->addText('departments', 'addTask.form.department')
			->setAttribute('placeholder', 'addTask.form.departments');

		$component['attachments'] = new MultipleUploadControl('attachments');
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
			$this->presenter->redirect('Homepage:');
		}

		if ($component['attachmentPreload']->isSubmittedBy()) {}

		if ($component['submit']->isSubmittedBy()) {

			// Reformat
			$values = $component->getValues();			

			// Adding owner.
			$values['owner'] = $this->presenter->getUser()->id;

			// Parsing tags
			if (isset($values['tags']) && $value = self::parseTags($values['tags'])) {
				$values['tags'] = $value;
			}

			//	Process store
			try {
				$task = $this->taskService->createTask((array)$values);
				$this->presenter->flashMessage('addTask.flashes.task_edited', 'alert-success');
				$this->presenter->redirect('detail', array('token' => $task->token));
			}
			catch (\RuntimeException $e) {
				$component->addError($e->getMessage());
			}
		}
	}



	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/AddTask.latte');
		$this->template->render();
	}



	/**
	 * String to array of tags
	 * 
	 * @param string $value
	 * 
	 * @return array
	 */
	private static function parseTags($value)
	{
		return Strings::split($value, '~[,;]\s*~');
	}



	/**
	 * Array of tags to string
	 * 
	 * @param array $tags
	 * 
	 * @return string
	 */
	private static function formatTags(array $tags = array())
	{
		return implode(',', $tags);
	}

}
