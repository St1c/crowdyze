<?php 
namespace Controls;

use Nette,
	Nette\Application\UI,
	Nette\Application\UI\Form,
	Nette\Application\UI\Control,
	Nette\Utils\Strings,
	Nette\Utils\Validators,
	Nette\Image;
use Taco\Nette\Forms\Controls\DateInput,
	Taco\Nette\Forms\Controls\MultipleUploadControl,
	Taco\Nette\Http\FileUploaded;
use Symfony\Component\Filesystem\Filesystem;


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
		
		$attachments = array();
		foreach ($task->related('task_attachment') as $attachment) {
			$attachments[] = new FileUploaded($attachment->path);
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
		
		$component->addSubmit('submit', 'addTask.form.submit');
		$component->addSubmit('cancel', 'addTask.form.cancel');

		//~ $component->onError[] 	= $this->processError;
		$component->onSuccess[] = $this->processSubmitted;

		return $component;
	}



	public function processError(Form $component)
	{
		//~ if ($this->isAjax() ) {
			//~ $this->invalidateControl();
		//~ }
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
			
			foreach ($values as $key => $value) {
				//	Exclude tags, upload, etc. from update
				$exclude = array('tags', 'upload', 'departments', 'attachments');
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

			// Saving attachments
			foreach ($values->attachments as $file) {
				if ($file instanceof Nette\Http\FileUpload) {
					$this->taskService->saveAttachment($task, $file);
				}
			}

			$this->taskService->storeTags($task, $value);

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
