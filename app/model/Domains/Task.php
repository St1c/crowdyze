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
	public $promotion;
	public $token;
	public $owner;



	/**
	 * Require id
	 */
	private function __construct($id)
	{
		$this->id = $id;
	}



	/**
	 * Vytvoření z pole.
	 * 
	 * @param array $entry Original, předlona
	 */
	public static function createFromArray(array $data)
	{
		$inst = new self(isset($data['id']) ? $data['id'] : Null);
		unset($data['id']);
		foreach ($data as $key => $val) {
			$setter = 'set' . ucfirst($key);
			if (method_exists($inst, $setter)) {
				$inst->$setter($val);
			}
			else {
				$inst->$key = $val;
			}
		}

		return $inst;
	}



	/**
	 * Vytvoření z ActiveRow. Špatný, špatný, ale kdo to má předělávat.
	 * 
	 * @param Nette\Database\Table\ActiveRow $data
	 */
	public static function createFromActiveRow(ActiveRow $data)
	{
		$inst = new self($data->id ?: Null);

		$inst->activeRow = $data;
		
		$inst->title 		= $data->title;
		$inst->description 	= $data->description;
		$inst->salary 		= $data->salary;
		$inst->budgetType 	= $data->budget_type;
		$inst->workers 		= $data->workers;
		$inst->deadline 	= $data->deadline;
		$inst->promotion 	= $data->promotion;
		$inst->token 		= $data->token;
		$inst->owner 		= $data->owner;

		return $inst;
	}


	public function getId()
	{
		return $this->id;
	}


	
	public function getDiscuss()
	{
		$ret = array();
		foreach ($this->activeRow->related('comment')->order('id') as $row) {
			$ret[] = Discuss::createFromActiveRow($row)->setTask($this);
		}
		
		return $ret;
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
