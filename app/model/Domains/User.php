<?php
namespace Model\Domains;


use Nette;
use Nette\Database\Table\ActiveRow;


class User extends Nette\Object
{

	private $id;
	public $token;
	public $firstName;
	public $lastName;
	public $profilePhoto;
	public $email;
	public $wallet;
	public $active;
	public $gender;
	public $city;
	public $country;
	public $banned;
	public $registered;



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
	 * 
	 * @return User
	 */
	public static function createFromArray(array $data)
	{
		$inst = new self(isset($data['id']) ? $data['id'] : Null);
		unset($data['id']);
		foreach ($data as $key => $val) {
			switch ($key) {
				case 'username':
					$inst->token = $val;
					break;
				case 'first_name':
					$inst->firstName = $val;
					break;
				case 'last_name':
					$inst->lastName = $val;
					break;
				case 'profile_photo':
					$inst->profilePhoto = $val;
					break;
				case 'email':
				case 'wallet':
				case 'active':
				case 'gender':
				case 'city':
				case 'country':
				case 'banned':
				case 'registered':
					$setter = 'set' . ucfirst($key);
					if (method_exists($inst, $setter)) {
						$inst->$setter($val);
					}
					else {
						$inst->$key = $val;
					}
				default:
					break;
			}
		}

		return $inst;
	}



	/**
	 * Vytvoření z ActiveRow. Špatný, špatný, ale kdo to má předělávat.
	 * 
	 * @param Nette\Database\Table\ActiveRow $data
	 * 
	 * @return User
	 */
	public static function createFromActiveRow(ActiveRow $data)
	{
		$inst = new self($data->id ?: Null);

		$inst->token = $data->username;
		$inst->firstName = $data->first_name;
		$inst->lastName = $data->last_name;
		$inst->profilePhoto = $data->profile_photo;
		$inst->email = $data->email;
		$inst->wallet = $data->wallet;
		$inst->active = $data->active;
		$inst->gender = $data->gender;
		$inst->city = $data->city;
		$inst->country = $data->country;
		$inst->banned = $data->banned;
		$inst->registered = $data->registered;

		return $inst;
	}



	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	
}
