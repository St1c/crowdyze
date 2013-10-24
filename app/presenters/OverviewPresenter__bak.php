<?php 

namespace App;

use Nette,
	Nette\Application\UI\Form,
	Model\Services\TasksService;

class OverviewPresenter__bak extends BasePresenter 
{
	/** @var Model\Services\TasksService @inject */
	public $tasksService;

	/** @var bool $editTitle */
	protected $editTitle;

	public function actionDefault()
	{
		$paginator = $this['paginator']->getPaginator();
		$this['paginator']->paginator->itemCount = $this->tasksService->count;
		$this->template->tasks = $this->tasksService->getTasks( $paginator->itemsPerPage, 
																$paginator->offset, 
																$this->getUser()->id);
	}

	public function actionTask($id)
	{

		$this->template->editTitle = $this->editTitle;
		$this->template->task = $this->tasksService->getTask($id);
		$this->template->userId = $this->getUser()->id;
	}

	public function createComponentEditTitleForm()
	{
		$form = new Form();

		$form->addText('title', 'Title:');
		$form->addHidden('id');
		$form->addSubmit('submit', 'Save');
		$form->addSubmit('cancel', 'Cancel');

		$form->onSubmit[] = $this->editTitleForm_Submit;

		return $form;
	}

	public function editTitleForm_Submit(Form $form)
	{
		$values = $form->getValues();
		
		if ($form['submit']->isSubmittedBy()) {
			$task = $this->tasksService->getTask($values->id);
			$task = $this->tasksService->update($task, array('title' => $values->title));
			$this->template->task = $task;
		}
		if ($form['cancel']->isSubmittedBy()) {
			$this->redirect('this');
		}

		$this->editTitle = FALSE;
		$this->template->editTitle = $this->editTitle;

		if ($this->isAjax()) {
			$this->invalidateControl('title');
		}
		else {
			$this->redirect('this');
		}
	}

	public function handleEditTitle($editTitle)
	{
		$this->editTitle = TRUE;
		$this->template->editTitle = $this->editTitle;

		$task = $this->tasksService->getTask($editTitle);

		if ($task === FALSE) {
			throw new BadRequestException('Záznam nebyl nalezen.');
		} else {
			$this['editTitleForm']->setDefaults(array(
				'title' => $task->title,
				'id' 	=> $task->id
			));
			$this->invalidateControl('title');
		}
	}

	public function actionTag($id)
	{
		$paginator = $this['paginator']->getPaginator();
		$this['paginator']->paginator->itemCount = $this->tasksService->getTagsTasksCount($id);

		$this->template->setFile(__DIR__ . '/../templates/Overview/default.latte');
		$this->template->tasks = $this->tasksService->getTaggedTasks($id, 
																	 $paginator->itemsPerPage, 
																	 $paginator->offset, 
																	 $this->getUser()->id);
	}

	protected function createComponentPaginator()
	{
		$paginator = new \Controls\PaginatorControl();
		$paginator->paginator->itemsPerPage = 24;
		return $paginator;
	}

	public function actionJtask($id)
	{
		$this->template->edit 	= $this->edit;
		$this->template->task 	= $this->tasksService->getTask($id);
		$this->template->userId = $this->getUser()->id;
	}

	public function handleJedit($elementId, $elementValue)
	{
		$this->edit = TRUE;
		$this->template->edit = $this->edit;

		$task = $this->tasksService->getTask($this->getParameter('id'));
		$this->tasksService->update($task, array($elementId => $elementValue));
		// $stop();
		$this->template->task = $task;
		$this->validateControl();
		$this->invalidateControl('task');
	}

	public function handleAcceptTask($id)
	{
		try {
			$this->tasksService->acceptTask($this->getUser()->id, $id);
		} catch (\Exception $e) {
			$this->flashMessage($e->getMessage(), 'alert-danger');
		}
	}
}