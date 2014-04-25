<?php
namespace Model\Domains;


use Nette;
use Nette\Database\Table\ActiveRow;


class Discuss extends Nette\Object
{

	private $id;
	private $created;
	public $content;
	public $author;
	private $task;



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
		$inst = new self($data->id ?: Null, $data->created ?: Null);

		return $inst;
	}



	/**
	 * Vytvoření z předlohy
	 * 
	 * @param self $entry Original, předlona
	 * @param int $id Možnost předefinovat povinnou položku.
	 */
	public static function createFromEntry(self $entry, $id = Null, \DateTime $created = Null)
	{
		$nuevo = clone $entry;
		if ($id) {
			$nuevo->id = $id;
		}
		if ($created) {
			$nuevo->created = $created;
		}

		return $nuevo;
	}



	public function getId()
	{
		return $this->id;
	}


	public function setTask(Task $value)
	{
		$this->task = $value;
		return $this;
	}


	public function getTask()
	{
		return $this->task;
	}


	public function getCreated()
	{
		return $this->created;
	}


}
