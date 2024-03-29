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

		$component->addText('deadline', 'addTask.form.deadline')
			->setDefaultValue(date('d/m/y',strtotime("+1 month")))
			->setAttribute('placeholder', 'addTask.form.deadline');
		
		// $component['deadline'] = new DateInput('Deadline:', DateInput::STYLE_SELECTS);
		// $component['deadline']->setDefaultValue((new DateTime())->add(new DateInterval('P1M')));
		
		$component->addText('departments', 'addTask.form.department')
			->setAttribute('placeholder', 'addTask.form.departments');

		$component->addRadioList('promotion', 'addTask.form.promotion', array('none', 'min', 'med', 'max'))
			->setDefaultValue(0);

		$component['attachments'] = new MultipleUploadControl('attachments');
		$component['attachments']->setMimeTypeClassFunction(function($type) {
			$p = explode('/', $type, 2);
			return \App\Helpers::mediaType($p[0]);
		});
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

			// Parsing date
			if (isset($values['deadline']) && $value = self::parseDate($values['deadline'])) {
				$values['deadline'] = $value;
			}

			//	Process store
			try {
				$task = $this->taskService->createTask((array)$values);
				$this->presenter->flashMessage('addTask.flashes.task_added', 'alert-success');
			}
			catch (\RuntimeException $e) {
				$component->addError(
					$this->parent->translator->translate($e->getMessage())
				);
				return;
			}
			$this->presenter->redirect('detail', array('token' => $task->token));
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


	/**
	 * Reformat date to format needed for recording in DB
	 * 
	 * @param string $value
	 * 
	 * @return string
	 */
	private static function parseDate($value)
	{
		$date = DateTime::createFromFormat('d/m/y', $value);
		return $date->format('Y-m-d');
	}
	
}
