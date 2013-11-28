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
	/** @var Model\Services\TaskService @inject */
	public $taskService;
	
	/** @var Model\Repositories\Budget_typeRepository @inject */
	public $budget_typeRepository;
	
	/** @var Model\Repositories\Department_nameRepository @inject */
	public $department_nameRepository;



	public function createComponentSingleTaskForm()
	{
		$budgetTypes = $this->budget_typeRepository->getAll();
		$departments = $this->department_nameRepository->getAll($this->presenter->getUser()->id);
		$task = $this->taskService->getTaskByToken($this->presenter->getParameter('id'));
		$tags = array();
		foreach ($task->related('task_has_tag') as $tag) {
			if ($tag->tag->tag) {
				$tags[] = $tag->tag->tag;
			}
		}

		$component = new Form();

		$component->setTranslator($this->parent->translator);

		$component->addText('title', 'addTask.form.title')
			->setAttribute('placeholder', 'addTask.form.title')
			->addrule(Form::FILLED, 'addTask.form.title_missing')
			->setDefaultValue($task->title);

		$component->addTextArea('description', 'addTask.form.description')
			->setAttribute('placeholder', 'addTask.form.description')
			->AddRule(Form::FILLED, 'addTask.form.description_missing')
			->setDefaultValue($task->description);
		
		$component->addText('budget', 'addTask.form.budget')
			->setAttribute('placeholder', 'addTask.form.budget')
			->AddRule(Form::FILLED, 'addTask.form.budget_missing')			
			->setDefaultValue($task->budget);
		
		$component->addSelect('budget_type', 'addTask.form.budget_type')
			->setItems($budgetTypes, TRUE)
			->setDefaultValue($task->budget_type);

		$component->addText('tags', 'addTask.form.tags')
			->setAttribute('placeholder', 'addTask.form.tags')
			->setDefaultValue(self::formatTags($tags));
		
		// $component->addUpload('upload', 'addTask.form.upload', TRUE);
		// 	// ->addCondition(Form::FILLED) // Image upload is not mandatory
		// 	// ->addRule(Form::IMAGE, 'Image must be JPEG, PNG or GIF.')
		// 	// ->addRule(Form::MAX_FILE_SIZE, 'Maximum file size is 64 kB', 64 * 1024 /* in bytes */);
		
		$component->addText('workers', 'addTask.form.workers_required')
			->setAttribute('placeholder', 'addTask.form.workers_required')
			->setDefaultValue($task->workers);
		
		$component->addText('deadline')
			->setAttribute('placeholder', 'addTask.form.deadline')
			->setDefaultValue($task->deadline);
		
		$component->addText('departments', 'addTask.form.department')
			->setAttribute('placeholder', 'addTask.form.departments');

		$component->addSubmit('submit', 'addTask.form.submit');
		$component->addSubmit('cancel', 'addTask.form.cancel');

		$component->onError[] 	= $this->processError;
		$component->onSubmit[] = $this->processSubmitted;

		return $component;
	}



	public function processError(Form $component)
	{
		if ($this->isAjax() ) {
			$this->invalidateControl();
		}
	}



	public function processSubmitted(Form $component)
	{
		$values = $component->getValues();
		
		if ($component['cancel']->isSubmittedBy()) {
			//~ if ($this->presenter->isAjax()) {
				//~ $this->presenter->invalidateControl('task');
			//~ }
		}
		
		if ($component['submit']->isSubmittedBy()) {
			$task = $this->taskService->getTaskByToken($this->presenter->getParameter('id'));
			foreach ($values as $key => $value) {
				if ($key == 'deadline') {
					$value = self::parseDateTime($value);
				}
				
				//	Do update nepatří akce tags, upload, ...
				in_array( $key, array('tags', 'upload', 'departments') ) || empty($value) ?: $update[$key] = $value;
			}

			$this->taskService->update($task, $update);
//~ dump($values);
//~ dump($update);
//~ dump($task);
//~ die('=========');


			// Saving tags
			if (isset($values['tags']) && $value = self::parseTags($values['tags'])) {
				$this->taskService->storeTags($task, $value);
			}

			// // Saving departments 
			// if ( !empty($values['departments']) ) {
			// 	$this->taskService->setDepartments($task, $values['departments']);
			// }

			// // Saving attachments
			// foreach ($values['upload'] as $upload) {
			// 	if ( $upload->isOk() ) {
			// 		$this->taskService->saveAttachment($task, $taskUUID, $upload);
			// 	}
			// }

			$this->presenter->flashMessage('addTask.flashes.task_edited', 'alert-success');
		}


		if ($this->presenter->isAjax()) {
			$this->presenter->invalidateControl('task');
		}
		
		$this->presenter->redirect('detail', array('id' => $this->presenter->getParameter('id')));
	}



	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/SingleTask.latte');
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



	/**
	 * @param string $value
	 * 
	 * @return DateTime
	 */
	private static function parseDateTime($value)
	{
		return \DateTime::createFromFormat('Y-m-d H:i:s', $value);
	}



	private static function formatTags(array $tags = array())
	{
		return implode(', ', $tags);
	}

}
