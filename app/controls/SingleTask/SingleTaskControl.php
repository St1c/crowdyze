<?php 
namespace Controls;

use Nette,
	Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control,
	Nette\Utils\Strings,
	Nette\Image;

class SingleTaskControl extends BaseControl
{
	/** @var Model\Services\TasksService @inject */
	public $tasksService;
	/** @var Model\Repositories\Tasks_budget_typesRepository @inject */
	public $tasks_budget_typesRepository;
	/** @var Model\Repositories\Departments_namesRepository @inject */
	public $departments_namesRepository;

	public function createComponentSingleTaskForm()
	{
		$budgetTypes 	= $this->tasks_budget_typesRepository->getAll();
		$departments 	= $this->departments_namesRepository->getAll($this->presenter->getUser()->id);

		$task 			= $this->tasksService->getTask($this->presenter->getParameter('id'));

		$singleTaskForm = new Form();

		$singleTaskForm->setTranslator($this->parent->translator);

		$singleTaskForm->addText('title', 'addTask.form.title')
			->setAttribute('placeholder', 'addTask.form.title')
			->addrule(Form::FILLED, 'addTask.form.title_missing')
			->setDefaultValue($task->title);

		$singleTaskForm->addTextArea('description', 'addTask.form.description')
			->setAttribute('placeholder', 'addTask.form.description')
			->AddRule(Form::FILLED, 'addTask.form.description_missing')
			->setDefaultValue($task->description);

		
		$singleTaskForm->addText('budget', 'addTask.form.budget')
			->setAttribute('placeholder', 'addTask.form.budget')
			->AddRule(Form::FILLED, 'addTask.form.budget_missing')			
			->setDefaultValue($task->budget);
		
		$singleTaskForm->addSelect('budget_type', 'addTask.form.budget_type')
			->setItems($budgetTypes, TRUE)
			->setDefaultValue($task->budget_type);

		$singleTaskForm->addText('tags', 'addTask.form.tags')
			->setAttribute('placeholder', 'addTask.form.tags');
		
		// $singleTaskForm->addUpload('upload', 'addTask.form.upload', TRUE);
		// 	// ->addCondition(Form::FILLED) // Image upload is not mandatory
		// 	// ->addRule(Form::IMAGE, 'Image must be JPEG, PNG or GIF.')
		// 	// ->addRule(Form::MAX_FILE_SIZE, 'Maximum file size is 64 kB', 64 * 1024 /* in bytes */);
		
		$singleTaskForm->addText('workers', 'addTask.form.workers_required')
			->setAttribute('placeholder', 'addTask.form.workers_required')
			->setDefaultValue($task->workers);
		
		$singleTaskForm->addText('deadline')
			->setAttribute('placeholder', 'addTask.form.deadline')
			->setDefaultValue($task->deadline);
		
		$singleTaskForm->addText('departments', 'addTask.form.department')
			->setAttribute('placeholder', 'addTask.form.departments');

		$singleTaskForm->addSubmit('submit', 'addTask.form.submit');
		$singleTaskForm->addSubmit('cancel', 'addTask.form.cancel');

		$singleTaskForm->onError[] 	= $this->singleTaskFormError;
		$singleTaskForm->onSubmit[] = $this->singleTaskFormSubmitted;

		return $singleTaskForm;
	}

	public function singleTaskFormError(Form $singleTaskForm)
	{
		if ($this->isAjax() ) {
			$this->invalidateControl();
		}
	}

	public function singleTaskFormSubmitted(Form $singleTaskForm)
	{
		$values = $singleTaskForm->getValues();
		
		if ($singleTaskForm['cancel']->isSubmittedBy()) {
			if ($this->presenter->isAjax()) {
				$this->presenter->invalidateControl('task');
			}
		}
		
		if ($singleTaskForm['submit']->isSubmittedBy()) {
			$task = $this->tasksService->getTask($this->presenter->getParameter('id'));

			foreach ($values as $key => $value) {
				if ($key == 'deadline') $value = date('Y-m-d H:i:s', strtotime($value));
				in_array( $key, array('tags', 'upload', 'departments') ) || empty($value) ?: $update[$key] = $value;
			}
			$this->tasksService->update($task, $update);

			// // Saving tags
			// if ( !empty($values['tags']) ) {
			// 	$this->tasksService->addTags($task, $values['tags']);
			// }

			// // Saving departments 
			// if ( !empty($values['departments']) ) {
			// 	$this->tasksService->setDepartments($task, $values['departments']);
			// }

			// // Saving attachments
			// foreach ($values['upload'] as $upload) {
			// 	if ( $upload->isOk() ) {
			// 		$this->tasksService->saveAttachment($task, $taskUUID, $upload);
			// 	}
			// }

			$this->presenter->flashMessage('addTask.flashes.task_edited', 'alert-success');
			$this->presenter->template->task = $task;
		}

		$this->presenter->edit = FALSE;
		$this->presenter->template->edit = $this->presenter->edit;

		if ($this->presenter->isAjax()) {
			$this->presenter->invalidateControl('task');
		}
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/SingleTask.latte');
		$this->template->render();
	}

}