<?php
namespace Model\Domains;


use Nette;
use Nette\Database\Table\ActiveRow;


class Discuss extends Nette\Object
{

	private $id;
	public $created;
	public $content;
	public $token;
	public $author;



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
			if (method_exists($setter)) {
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

		return $inst;
	}


	public function getId()
	{
		return $this->id;
	}


}
