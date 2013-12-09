<?php
namespace Model\Domains;


use Nette;
use Nette\Database\Table\ActiveRow;


class Task extends Nette\Object
{

	/**
	 * @var Nette\Database\Table\ActiveRow
	 */
	private $activeRow;
	
	private $id;
	public $title;
	public $description;
	public $salary;
	public $budgetType;
	public $workers;
	public $deadline;
	public $token;
	public $owner;


	/**
	 * @param Nette\Database\Table\ActiveRow $data
	 */
	public function __construct(ActiveRow $data)
	{
		$this->activeRow = $data;
		
		$this->id = $data->id;
		$this->title = $data->title;
		$this->description = $data->description;
		$this->salary = $data->salary;
		$this->budgetType = $data->budget_type;
		$this->workers = $data->workers;
		$this->deadline = $data->deadline;
		$this->token = $data->token;
		$this->owner = $data->owner;
	}


	public function getId()
	{
		return $this->id;
	}



	public function getActiveRow()
	{
		return $this->activeRow;
	}



	public function related($what)
	{
		return $this->activeRow->related($what);
	}



	public function ref($a, $b)
	{
		return $this->activeRow->ref($a, $b);
	}

}
