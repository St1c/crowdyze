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
	/** @var Model\Services\TasksService @inject */
	public $tasksService;
	/** @var Model\Repositories\Tasks_budget_typesRepository @inject */
	public $tasks_budget_typesRepository;
	/** @var Model\Repositories\Departments_namesRepository @inject */
	public $departments_namesRepository;

	public function createComponentAddTaskForm()
	{
		$addTask = new Form();
		$addTask->setTranslator($this->parent->translator);

		$budgetTypes = $this->tasks_budget_typesRepository->getAll();
		$departments = $this->departments_namesRepository->getAll($this->presenter->getUser()->id);

		$addTask->addText('title', 'addTask.form.title')
			->setAttribute('placeholder', 'addTask.form.title')
			->addrule(Form::FILLED, 'addTask.form.title_missing');
		
		$addTask->addTextArea('description', 'addTask.form.description')
			->setAttribute('placeholder', 'addTask.form.description')
			->AddRule(Form::FILLED, 'addTask.form.description_missing');
		
		$addTask->addText('budget', 'addTask.form.budget')
			->setAttribute('placeholder', 'addTask.form.budget')
			->AddRule(Form::FILLED, 'addTask.form.budget_missing');
		
		$addTask->addSelect('budget_type', 'addTask.form.budget_type')
			->setItems($budgetTypes, TRUE)
			->setDefaultValue(3);

		$addTask->addText('tags', 'addTask.form.tags')
			->setAttribute('placeholder', 'addTask.form.tags');
		
		$addTask->addUpload('upload', 'addTask.form.upload', TRUE);
			// ->addCondition(Form::FILLED) // Image upload is not mandatory
			// ->addRule(Form::IMAGE, 'Image must be JPEG, PNG or GIF.')
			// ->addRule(Form::MAX_FILE_SIZE, 'Maximum file size is 64 kB', 64 * 1024 /* in bytes */);
		
		$addTask->addText('workers', 'addTask.form.workers_required')
			->setAttribute('placeholder', 'addTask.form.workers_required');
		
		$addTask->addText('deadline')
			->setAttribute('placeholder', 'addTask.form.deadline');
		
		$addTask->addText('departments', 'addTask.form.department')
			->setAttribute('placeholder', 'addTask.form.departments');

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
			$task = $this->tasksService->createTask($user_id, $formValues);

			// Saving tags
			if ( !empty($formValues['tags']) ) {
				$this->tasksService->addTags($task, $formValues['tags']);
			}

			// Saving departments 
			if ( !empty($formValues['departments']) ) {
				$this->tasksService->setDepartments($task, $formValues['departments']);
			}

			// Saving attachments
			foreach ($formValues['upload'] as $upload) {
				if ( $upload->isOk() ) {
					$this->tasksService->saveAttachment($task, $taskUUID, $upload);
				}
			}

			$this->presenter->flashMessage('addTask.flashes.task_added', 'alert-success');
			$this->presenter->redirect('Overview:');

		} catch (Nette\InvalidArgumentException $e) {
			
			if ($this->presenter->isAjax() ) {
				$this->presenter->invalidateControl();
			}

		}
	}

	public function handleSaveImage()
	{
		$values = $this->request->files;
		$out = $this->presenter->context->httpRequest->url->baseUrl . $values['avatar_path'];
		$this->sendResponse(new Nette\Application\Responses\JsonResponse(array('src' => $out)));
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/AddTask.latte');
		$this->template->render();
	}

}