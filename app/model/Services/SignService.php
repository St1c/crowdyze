<?php
namespace Model\Services;

use Nette,
	Nette\Security\AuthenticationException,
	Nette\Database\Table\ActiveRow,
	Nette\Http\FileUpload,
	Nette\Utils\Strings,
	Model\Repositories,
	Model\Services,
	Utilities;

class SignService extends Nette\Object
{

	/** @var userRepository */
	private $userRepository;
	
	/** @var fileManager */
	private $fileManager;
	
	/** @var Utilities\MailerService */
	protected $mailerService;
	

	
	public function __construct(
		Repositories\UserRepository $userRepository,
		Services\FileManager $fileManager,
		Utilities\MailerService $mailerService 
	) {
		$this->userRepository 			= $userRepository;
		$this->fileManager 				= $fileManager;
		$this->mailerService 			= $mailerService;
	}


	/**
	 * Get user's data
	 * 
	 * @param  array 	$by 	Searching attributes
	 * 
	 * @return ActiveRow    	User's data
	 */
	public function find($by)
	{
		return $this->userRepository->find($by);
	}


	/**
	 * Update user details
	 * 
	 * @param  ActiveRow $user 
	 * @param  array     $data 
	 * 
	 * @return ActiveRow       
	 */
	public function update(ActiveRow $user, array $data)
	{
		return $this->userRepository->update($user, $data);
	}


	/**
	 * Get user's details by ID
	 * 
	 * @param  int  	$id User's id
	 * 
	 * @return ActiveRow    User's details
	 */
	public function getUserData($id)
	{
		return $this->userRepository->find(array('id' => $id));
	}


	/**
	 * Register New User
	 * 
	 * @param  string  	$type   Type of registration: email|facebook|google
	 * @param  array  	$data   User's data
	 * @param  integer 	$active Activate profile
	 * 
	 * 
	 * @return ActiveRow        New record in DB
	 */
	public function register($type, array $data, $active = 1)
	{
		switch ($type) {
			case 'facebook':
				$user = $this->registerFromFacebook($data, $active);
				break;
			
			case 'google':
				$user = $this->registerFromGoogle($data, $active);
				break;

			default:
				$user = $this->registerFromEmail($data, $active);
				break;
		}

		// Send welcome mail to a new user
		$this->mailerService->sendWelcomeMail($data['email']);

		return $user;
	}


	/**
	 * Update User's data in DB
	 * 
	 * @param  string  		$type   Type of registration: email|facebook|google
	 * @param  ActiveRow  	$user   User's DB row
	 * @param  integer 		$data 	User's data
	 * 
	 * @return ActiveRow        	New record in DB
	 */
	public function updateFromSocial($type, $user, $data)
	{
		switch ($type) {
			case 'facebook':
				$this->updateFromFacebook($user, $data);
				break;
			
			case 'google':
				$this->updateFromGoogle($user, $data);
				break;
		}
	}


	/**
	 * Performs email registration of the new user
	 *
	 * @param array $data 		Form data
	 * @param int 	$setActive 	Activate profile
	 *
	 * @throws AuthenticationException If Email is already registered
	 */
	private function registerFromEmail(array $data, $setActive)
	{
		if ($this->userRepository->find(array('email' => $data['email']))) {
			throw new AuthenticationException('Email already registered.');
		}

		return $this->userRepository->create(array(
			'email' 	=> $data['email'],
			'password' 	=> sha1($data['password']),
			'role'		=> 'user',
			'active'	=> $setActive,
			'wallet' 	=> 0
		));
	}


	/**
	 * Register new Facebook user to the database
	 * 
	 * @param array 	$me 		Facebook user profile info array
	 * @param int|NULL 	$setActive 	Register and activate profile
	 * 
	 * @return Nette\Database\Table\ActiveRow
	 */
	private function registerFromFacebook(array $me, $setActive = 1)
	{
		return $this->userRepository->create(array(
			'email' 		=> $me['email'],
			'role' 			=> 'user',
			'active'		=> $setActive,
			'wallet'	 	=> 0,
			'facebook_id' 	=> $me['id'],
			'first_name'	=> $me['first_name'],
			'last_name'		=> $me['last_name'],
			'gender'		=> $me['gender'],
		));
	}


	/**
	 * Register new Google user to the database
	 * 
	 * @param array 	$me 		Google user profile info array
	 * @param int|NULL 	$setActive 	Register and activate profile
	 * 
	 * @return Nette\Database\Table\ActiveRow
	 */
	private function registerFromGoogle(array $me, $setActive = 1)
	{
		return $this->userRepository->create(array(
			'email' 		=> $me['email'],
			'role' 			=> 'user',
			'active'		=> $setActive,
			'wallet'	 	=> 0,
			'google_id' 	=> $me['id'],
			'first_name'	=> $me['given_name'],
			'last_name'		=> $me['family_name'],
			'gender'		=> $me['gender'],
		));
	}



	/**
	 * Update missing data in the user table
	 * 
	 * @param Nette\Database\Table\ActiveRow 	$user User's DB table
	 * @param array 							$me Facebook user profile info array
	 */
	private function updateFromFacebook($user, array $me)
	{
		$updateData = array();

		if ( empty($user->first_name) ) {
			$updateData['first_name'] = $me['first_name'];
		}

		if ( empty($user->last_name) ) {
			$updateData['last_name'] = $me['last_name'];
		}

		if ( empty($user->facebook_id) ) {
			$updateData['facebook_id'] = $me['id'];
		}

		if ( empty($user->gender) ) {
			$updateData['gender'] = $me['gender'];
		}

		if ( empty($user->profile_photo) ) {
			$photo = "https://graph.facebook.com/" . $me['id'] . "/picture?type=large";
			$updateData['profile_photo'] = $this->fileManager->saveProfilePhoto('users', uniqid(), $photo);
		}

		// Update changes
		if (!empty($updateData)) {
			$this->userRepository->update($user, $updateData);
		}
	}


	/**
	 * Update missing data in the user table
	 * 
	 * @param Nette\Database\Table\ActiveRow 	$user 	DB table
	 * @param array 							$me 	Facebook user profile info array
	 */
	private function updateFromGoogle($user, array $me)
	{
		$updateData = array();

		if ( empty($user->first_name) ) {
			$updateData['first_name'] = $me['given_name'];
		}

		if ( empty($user->last_name) ) {
			$updateData['last_name'] = $me['family_name'];
		}

		if ( empty($user->google_id) ) {
			$updateData['google_id'] = $me['id'];
		}

		if ( empty($user->gender) ) {
			$updateData['gender'] = $me['gender'];
		}
		
		if ( empty($user->profile_photo) ) {
			$updateData['profile_photo'] = $this->fileManager->saveProfilePhoto('users', uniqid(), $me['picture']);
		}
		
		// Update changes
		if (!empty($updateData)) {
			$this->userRepository->update($user, $updateData);
		}
	}


	/**
	 * Create user's Identity
	 * 
	 * @param  ActiveRow $user User's details
	 * 
	 * @return Identity        User's identity
	 */
	public function createIdentity(ActiveRow $user)
	{
		return $this->userRepository->createIdentity($user);
	}
}
