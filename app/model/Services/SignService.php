<?php
namespace Model\Services;

use Nette,
	Nette\Security\AuthenticationException,
	Nette\Database\Table\ActiveRow,
	Nette\Http\FileUpload,
	Nette\Utils\Strings,
	Model\Repositories,
	Utilities;

class SignService extends Nette\Object
{

	/** @var userRepository */
	private $userRepository;
	/** @var user_detailsRepository */
	private $user_detailsRepository;
	/** @var WalletRepository */
	private $walletRepository;
	/** @var fileManager */
	private $fileManager;
	/** @var Utilities\MailerService */
	protected $mailerService;
	
	public function __construct(Repositories\UserRepository $userRepository,
								Repositories\User_detailsRepository $user_detailsRepository,
								Repositories\WalletRepository $walletRepository,
								Utilities\FileManager $fileManager,
								Utilities\MailerService $mailerService )
	{
		$this->userRepository 			= $userRepository;
		$this->user_detailsRepository 	= $user_detailsRepository;
		$this->walletRepository 		= $walletRepository;
		$this->fileManager 				= $fileManager;
		$this->mailerService 			= $mailerService;
	}


	/**
	 * Get user's data
	 * 
	 * @param  array 	$by 	Searching attributes
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
	 * @return ActiveRow       
	 */
	public function update(ActiveRow $user, array $data)
	{
		return $user->related('user_details')->update($data);
	}


	/**
	 * Get user's details by ID
	 * 
	 * @param  int  	$id User's id
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
	 * @return ActiveRow        New record in DB
	 */
	public function register($type, array $data, $active = 1)
	{
		switch ($type) {
			case 'facebook':
				$user = $this->registerSocial($data, $active);
				$this->setFacebookDetails($user, $data);
				break;
			
			case 'google':
				$user = $this->registerSocial($data, $active);
				$this->setGoogleDetails($user, $data);
				break;

			default:
				$user = $this->registerEmail($data, $active);
				break;
		}

		// Create new wallet for the user
		$this->createUserWallet($user->id);
		
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
	 */
	private function registerEmail(array $data, $setActive)
	{
		if ($this->userRepository->find(array('email' => $data['email']))) {
			throw new AuthenticationException('Email already registered.');
		}

		return $this->userRepository->create(array(
			'email' 	=> $data['email'],
			'password' 	=> sha1($data['password']),
			'role'		=> 'user',
			'active'	=> $setActive
		));
	}


	/**
	 * Register new Facebook or Google user to the database
	 * 
	 * @param array 	$me 		Facebook or Google user profile info array
	 * @param int|NULL 	$setActive 	Register and activate profile
	 * @return Nette\Database\Table\ActiveRow
	 */
	private function registerSocial(array $me, $setActive = 1)
	{
		return $this->userRepository->create(array(
			'email' 		=> $me['email'],
			'role' 			=> 'user',
			'active'		=> $setActive,
		));
	}


	/**
	 * Register new Facebook user to the database
	 * 
	 * @param Nette\Database\Table\ActiveRow	$user 	Users DB Table
	 * @param array 							$me 	Facebook user profile info array
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function setFacebookDetails($user, array $me)
	{
		return $user->related('user_details')->insert(array(
			'facebook_id' 	=> $me['id'],
			'first_name'	=> $me['first_name'],
			'last_name'		=> $me['last_name'],
			'gender'		=> $me['gender'],
		));
	}


	/**
	 * Register new Google user to the database
	 * 
	 * @param Nette\Database\Table\ActiveRow 	$user 	User's table
	 * @param array 							$me 	Google user profile info array
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function setGoogleDetails($user, array $me)
	{
		return $user->related('user_details')->insert(array(
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
	 * @return Nette\Database\Table\ActiveRow
	 */
	private function updateFromFacebook($user, array $me)
	{
		$updateData = array();
		$user_details = $this->user_detailsRepository->detailsExists($user->id);

		if (!$user_details) {
			$this->setFacebookDetails($user, $me);
		} else {

			if ( empty($user_details->first_name) ) {
				$updateData['first_name'] = $me['first_name'];
			}

			if ( empty($user_details->last_name) ) {
				$updateData['last_name'] = $me['last_name'];
			}

			if ( empty($user_details->facebook_id) ) {
				$updateData['facebook_id'] = $me['id'];
			}

			if ( empty($user_details->gender) ) {
				$updateData['gender'] = $me['gender'];
			}

			if ( empty($user_details->profile_photo) ) {
				$photo = "https://graph.facebook.com/" . $me['id'] . "/picture?type=large";
				$updateData['profile_photo'] = $this->fileManager->saveProfilePhoto('users', uniqid(), $photo);
			}

			// Update changes
			if (!empty($updateData)) {
				$this->user_detailsRepository->update($user_details, $updateData);
			}
		}
	}


	/**
	 * Update missing data in the user table
	 * 
	 * @param Nette\Database\Table\ActiveRow 	$user 	DB table
	 * @param array 							$me 	Facebook user profile info array
	 * @return Nette\Database\Table\ActiveRow
	 */
	private function updateFromGoogle($user, array $me)
	{
		$updateData = array();
		$user_details = $this->user_detailsRepository->detailsExists($user->id);

		if (!$user_details) {
			$this->setGoogleDetails($user, $me);
		} else {

			if ( empty($user_details->first_name) ) {
				$updateData['first_name'] = $me['given_name'];
			}

			if ( empty($user_details->last_name) ) {
				$updateData['last_name'] = $me['family_name'];
			}

			if ( empty($user_details->google_id) ) {
				$updateData['google_id'] = $me['id'];
			}

			if ( empty($user_details->gender) ) {
				$updateData['gender'] = $me['gender'];
			}
			
			if ( empty($user_details->profile_photo) ) {
				$updateData['profile_photo'] = $this->fileManager->saveProfilePhoto('users', uniqid(), $me['picture']);
			}
			
			// Update changes
			if (!empty($updateData)) {
				$this->user_detailsRepository->update($user_details, $updateData);
			}
		}
	}


	private function createUserWallet($userId)
	{
		$this->walletRepository->create(array(
			'user_id' => $userId,
			'balance' => 0
		));
	}


	/**
	 * Create user's Identity
	 * 
	 * @param  ActiveRow $user User's details
	 * @return Identity        User's identity
	 */
	public function createIdentity(ActiveRow $user)
	{
		return $this->userRepository->createIdentity($user);
	}
}
