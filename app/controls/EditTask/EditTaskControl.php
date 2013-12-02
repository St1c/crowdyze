<?php 
namespace Controls;

use Nette,
	Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control,
	Nette\Utils\Strings,
	Nette\Image;

class EditTaskControl extends BaseControl
{
	/** @var Model\Services\TaskService @inject */
	public $taskService;
	
	/** @var Model\Repositories\Budget_typeRepository @inject */
	public $budget_typeRepository;
	
	/** @var Model\Repositories\Department_nameRepository @inject */
	public $department_nameRepository;



	public function createComponentEditTaskForm()
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
		
		$component->addText('salary', 'addTask.form.salary')
			->setAttribute('placeholder', 'addTask.form.salary')
			->AddRule(Form::FILLED, 'addTask.form.salary_missing')			
			->setDefaultValue($task->salary);
		
		$component->addSelect('budget_type', 'addTask.form.budget_type')
			->setItems($budgetTypes, TRUE)
			->setDefaultValue($task->budget_type);

		$component->addText('tags', 'addTask.form.tags')
			->setAttribute('placeholder', 'addTask.form.tags')
			->setDefaultValue(self::formatTags($tags));
		
		$component->addText('workers', 'addTask.form.workers_required')
			->setAttribute('placeholder', 'addTask.form.workers_required')
			->setDefaultValue($task->workers);

		$component->addSelect('day', 'day')
			->setItems(array(1 => 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31))
			->setDefaultValue(  date('j',$task->deadline->getTimeStamp()) )
			->setAttribute('placeholder', 'addTask.form.day');

		$component->addSelect('month')
			->setItems(array( 1 => 1,2,3,4,5,6,7,8,9,10,11,12))
			->setDefaultValue( date('n',$task->deadline->getTimeStamp()) )
			->setAttribute('placeholder', 'addTask.form.month');

		$component->addText('year')
			->setDefaultValue(  date('Y',$task->deadline->getTimeStamp()) )
			->setAttribute('size', 4)
			->setAttribute('placeholder', 'addTask.form.year');
		
		$component->addText('departments', 'addTask.form.department')
			->setAttribute('placeholder', 'addTask.form.departments');

		// $component->addUpload('upload', 'addTask.form.upload', TRUE);
		// 	// ->addCondition(Form::FILLED) // Image upload is not mandatory
		// 	// ->addRule(Form::IMAGE, 'Image must be JPEG, PNG or GIF.')
		// 	// ->addRule(Form::MAX_FILE_SIZE, 'Maximum file size is 64 kB', 64 * 1024 /* in bytes */);

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
			// if ($this->presenter->isAjax()) {
				// $this->presenter->invalidateControl('task');
			// }
		}
		
		if ($component['submit']->isSubmittedBy()) {

			$values['budget'] = self::calculateBudget($values);
			$values['deadline'] = self::parseDate($values);
			
			foreach ($values as $key => $value) {
				// if ($key == 'deadline') {
				// 	$value = self::parseDateTime($value);
				// }
				
				//	Exclude tags, upload, etc. from update
				$exclude = array('tags', 'upload', 'departments', 'day', 'month', 'year');
				in_array( $key,  $exclude ) || empty($value) ?: $update[$key] = $value;
			}

			$task = $this->taskService->getTaskByToken($this->presenter->getParameter('token'));
			$this->taskService->update($task, $update);

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
		
		$this->presenter->redirect('detail', array('token' => $this->presenter->getParameter('token')));
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



	// /**
	//  * @param string $value
	//  * 
	//  * @return DateTime
	//  */
	// private static function parseDateTime($value)
	// {
	// 	return \DateTime::createFromFormat('Y-m-d H:i:s', $value);
	// }



	/**
	 * Form input date to desirable format
	 * 
	 * @param  array $values Form Values
	 * 
	 * @return Date
	 */
	private static function parseDate($values)
	{
		return date($values['year'] . '-' . $values['month'] . '-' . $values['day']);
	}



	private static function formatTags(array $tags = array())
	{
		return implode(',', $tags);
	}



	/**
	 * Calclate the final costs for the campaign
	 * 
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
}
