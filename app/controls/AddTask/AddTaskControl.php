<?php 
namespace Controls;

use Nette,
	Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control,
	Nette\Utils\Strings,
	Nette\Image;

class AddTaskControl extends BaseControl
{
	/** @var Model\Services\TaskService @inject */
	public $taskService;

	/** @var Model\Services\UserService @inject */
	public $userService;

	/** @var Model\Repositories\Budget_typeRepository @inject */
	public $budget_typeRepository;

	/** @var Model\Repositories\Department_nameRepository @inject */
	public $department_nameRepository;

	/** @var Utilities\FileManager @inject */
	public $fileManager;

	public function createComponentAddTaskForm($name)
	{
		$addTask = new Form($this, $name);
		$addTask->setTranslator($this->parent->translator);

		$budgetTypes = $this->budget_typeRepository->getAll();
		$departments = $this->department_nameRepository->getAll($this->presenter->getUser()->id);

		$addTask->addText('title', 'addTask.form.title')
			->setAttribute('placeholder', 'addTask.form.title')
			->addrule(Form::FILLED, 'addTask.form.title_missing');
		
		$addTask->addTextArea('description', 'addTask.form.description')
			->setAttribute('placeholder', 'addTask.form.description')
			->AddRule(Form::FILLED, 'addTask.form.description_missing');
		
		$addTask->addText('salary', 'addTask.form.salary')
			->setAttribute('placeholder', 'addTask.form.salary')
			->setAttribute('size', 6)
			->AddRule(Form::FILLED, 'addTask.form.salary_missing');
		
		$addTask->addSelect('budget_type', 'addTask.form.budget_type')
			->setItems($budgetTypes, TRUE)
			->setDefaultValue(3);

		$addTask->addText('tags', 'addTask.form.tags')
			->setAttribute('placeholder', 'addTask.form.tags');
		
		$addTask->addText('workers', 'addTask.form.workers_required')
			->setDefaultValue(1)
			->setAttribute('placeholder', 'addTask.form.workers_required');
		
		$addTask->addSelect('day', 'day')
			->setItems(array_combine(range(1,31), range(1,31)))
			->setDefaultValue(  date('j',strtotime("+1 month")) )
			->setAttribute('placeholder', 'addTask.form.day');

		$addTask->addSelect('month')
			->setItems(array_combine(range(1, 12), range(1,12)))
			->setDefaultValue( date('n',strtotime("+1 month")) )
			->setAttribute('placeholder', 'addTask.form.month');

		$addTask->addText('year')
			->setDefaultValue(  date('Y',strtotime("+1 month")) )
			->setAttribute('size', 4)
			->setAttribute('placeholder', 'addTask.form.year');
		
		$addTask->addText('departments', 'addTask.form.department')
			->setAttribute('placeholder', 'addTask.form.departments');

		// $addTask->addMultipleFileUpload('upload', 'addTask.form.upload', TRUE);
			// ->addCondition(Form::FILLED) // Image upload is not mandatory
			// ->addRule(Form::IMAGE, 'Image must be JPEG, PNG or GIF.')
			// ->addRule(Form::MAX_FILE_SIZE, 'Maximum file size is 64 kB', 64 * 1024 /* in bytes */);

		$addTask->addHidden('token', $this->taskService->generateTaskToken());

		$addTask->addSubmit('submit', 'addTask.form.submit');
		
		$addTask->onError[] 	= $this->processError;
		$addTask->onSuccess[] 	= $this->processSubmitted;

		return $addTask;
	}

	public function processError(Form $addTask)
	{

		if ($this->presenter->isAjax() ) {
			$this->invalidateControl();
		}

	}

	public function processSubmitted(Form $addTask)
	{	
		$user_id 	= $this->presenter->getUser()->id;
		$form 		= $addTask->getValues(TRUE); // TRUE = get values as an ordinary array
		$taskUUID	= uniqid();

		try {
			// Saving task details
			$form = $this->saveTaskDetails($form);

			// Allocate money for the task from user's wallet
			$this->userService->reserveBudget($form['budget']);

			$this->presenter->flashMessage('addTask.flashes.task_added', 'alert-success');
			$this->presenter->redirect('Homepage:');

		} catch (Nette\InvalidArgumentException $e) {
			$this->presenter->flashMessage($e->getMessage(), 'alert-danger');
			if ($this->presenter->isAjax() ) {
				$this->presenter->invalidateControl();
			}

		}
	}

	/**
	 * Save task details 
	 * 
	 * @param  Form $form
	 */
	private function saveTaskDetails($form)
	{

			// Convert date to TIMESTAMP SQL format
			$form['deadline'] 	= self::parseDate($form);
			$form['budget'] 	= self::calculateBudget($form);

			$task = $this->taskService->createTask($user_id, $form);

			// Saving tags
			if ( !empty($form['tags']) ) {
				$this->taskService->storeTags($task, $form['tags']);
			}

			// Saving departments 
			if ( !empty($form['departments']) ) {
				$this->taskService->setDepartments($task, $form['departments']);
			}

			// Saving attachments
			// foreach ($form['upload'] as $upload) {
			// 	if ( $upload->isOk() ) {
			// 		$this->taskService->saveAttachment($task, $taskUUID, $upload);
			// 	}
			// }
	}


	/**
	 * Calclate the final costs for the campaign
	 * 
	 * @param  array $values Form values
	 * 
	 * @return int           Final budget
	 */
	private static function calculateBudget($values)
	{
		$commissionPerc = 1.05;
		$commissionFix 	= 0.50;

		switch ($values['budget_type']) {
			case '1': 
				// Pay the best
				$budget = $values['salary'] * $commissionPerc + $commissionFix;
				break;
			
			case '2': 
				// Pay the best 10
				$budget = (10 * $values['salary'] * $commissionPerc) + $commissionFix;

			default: 
				// Pay all
				$budget = ($values['workers'] * $values['salary'] * $commissionPerc) + $commissionFix;
				break;
		}

		return $budget;
	}



	/**
	 * Form input date to desirable format
	 * 
	 * @param  array $values Form Values
	 *
	 * @throws InvalidArgumentException If the date is not valid
	 * @return Date
	 */
	private static function parseDate($values)
	{
		if (!checkdate($values['month'], $values['day'], $values['year'])) {
			throw new Nette\InvalidArgumentException("Not a valid date", 1);
		}
		return date($values['year'] . '-' . $values['month'] . '-' . $values['day']);
	}



	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/AddTask.latte');
		$this->template->render();
	}

}
