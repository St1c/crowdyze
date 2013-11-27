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
			->setDefaultValue(10)
			->setAttribute('placeholder', 'addTask.form.workers_required');
		
		$addTask->addSelect('day', 'day')
			->setItems(array(1 => 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31))
			->setDefaultValue(  date('d',strtotime("+1 month")) )
			->setAttribute('placeholder', 'addTask.form.day');

		$addTask->addSelect('month')
			->setItems(array( 1 => 1,2,3,4,5,6,7,8,9,10,11,12))
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
		
		$addTask->onError[] 	= $this->addTaskFormError;
		$addTask->onSuccess[] 	= $this->addTaskFormSubmitted;

		return $addTask;
	}

	public function addTaskFormError(Form $addTask)
	{

		if ($this->presenter->isAjax() ) {
			$this->invalidateControl();
		}

	}

	public function addTaskFormSubmitted(Form $addTask)
	{	
		$user_id 		= $this->presenter->getUser()->id;
		$formValues 	= $addTask->getValues(TRUE); // TRUE = get values as an ordinary array
		$taskUUID		= uniqid();

		try {
			// Saving task details
			
			// Convert date to TIMESTAMP SQL format
			$formValues['deadline'] = date($formValues['year'] . '-' . $formValues['month'] . '-' . $formValues['day']);

			$formValues['budget'] = $this->calculateBudget($formValues);

			$task = $this->taskService->createTask($user_id, $formValues);

			// Saving tags
			if ( !empty($formValues['tags']) ) {
				$this->taskService->addTags($task, $formValues['tags']);
			}

			// Saving departments 
			if ( !empty($formValues['departments']) ) {
				$this->taskService->setDepartments($task, $formValues['departments']);
			}

			// Saving attachments
			// foreach ($formValues['upload'] as $upload) {
			// 	if ( $upload->isOk() ) {
			// 		$this->taskService->saveAttachment($task, $taskUUID, $upload);
			// 	}
			// }

			$this->presenter->flashMessage('addTask.flashes.task_added', 'alert-success');
			$this->presenter->redirect('Homepage:');

		} catch (Nette\InvalidArgumentException $e) {
			
			if ($this->presenter->isAjax() ) {
				$this->presenter->invalidateControl();
			}

		}
	}


	/**
	 * Calclate the final costs for the campaign
	 * @param  array $values Form values
	 * @return int           Final budget
	 */
	private function calculateBudget($values)
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

	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/AddTask.latte');
		$this->template->render();
	}

}