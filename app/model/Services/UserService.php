<?php
namespace Model\Services;

use Nette,
	Nette\Security\AuthenticationException,
	Nette\Database\Table\ActiveRow,
	Nette\Http\FileUpload,
	Nette\Utils\Strings,
	Model\Repositories,
	Utilities;

class UserService extends Nette\Object
{

	/** @var userRepository */
	private $userRepository;

	/** @var user_detailsRepository */
	private $user_detailsRepository;
	
	/** @var accepted_taskRepository */
	private $accepted_taskRepository;
	
	/** @var Repositories\WalletRepository */
	private $walletRepository;
	
	/** @var fileManager */
	private $fileManager;
	
	/** @var Utilities\MailerService */
	protected $mailerService;
	


	public function __construct(
		Repositories\UserRepository $userRepository,
		Repositories\User_detailsRepository $user_detailsRepository,
		Repositories\Accepted_taskRepository $accepted_taskRepository,
		Repositories\WalletRepository $walletRepository,
		Utilities\FileManager $fileManager,
		Utilities\MailerService $mailerService
	) {
		$this->userRepository 			= $userRepository;
		$this->user_detailsRepository 	= $user_detailsRepository;
		$this->accepted_taskRepository  = $accepted_taskRepository;
		$this->walletRepository 		= $walletRepository;
		$this->fileManager 				= $fileManager;
		$this->mailerService 			= $mailerService;
	}


	/**
	 * Get user's data
	 * 
	 * @param  array $by 	Searching attributes
	 * 
	 * @return ActiveRow   	User's data
	 */
	public function find($by)
	{
		return $this->userRepository->find($by);
	}


	/**
	 * Get user's details by ID
	 * 
	 * @param  int $id 		User's id
	 * 
	 * @return ActiveRow    User's details
	 */
	public function getUserData($id)
	{
		return $this->userRepository->find(array('id' => $id));
	}


	/**
	 * Update profile
	 * 
	 * @param  ActiveRow $user 
	 * @param  array     $data 
	 * 
	 * @return ActiveRow          
	 */
	public function update(ActiveRow $user, array $data)
	{
		return $user->related('user_details')->update($data);
	}


	/**
	 * Update details from profile Form
	 * 
	 * @param  ActiveRow $user 
	 * @param  array     $data 
	 * 
	 * @return ActiveRow 
	 */
	public function updateFromProfile(ActiveRow $user, array $data)
	{
		$user_details = $this->user_detailsRepository->detailsExists($user->id);
		if (!$user_details) {
			$user->related('user_details')->insert($data);
		} else {
			return $user->related('user_details')->update($data);
		}
	}


	/**
	 * Get user cash balance
	 * 
	 * @param  int $userId
	 * 
	 * @return string 
	 */
	public function getBalance($userId)
	{
		return $this->walletRepository->getBalance($userId);
	}


	/**
	 * Add credit to the user's account
	 * 
	 * @param int $userId 
	 * @param int $amount 
	 *
	 * @return ActiveRow
	 */
	public function addBalance($userId, $amount)
	{	
		$wallet = $this->walletRepository->get(array('user_id' => $userId));
		$credit = $wallet->balance + $amount;
		return $this->walletRepository->update($wallet, array('balance' => $credit));
	}


	/**
	 * Get user's accepted tasks
	 * 
	 * @param  int 	$userId User's ID
	 * 
	 * @return ActiveRow   	Results
	 */
	public function getAcceptedUserTasks($userId)
	{
		return $this->accepted_taskRepository->getAcceptedByUser($userId);
	}


	/**
	 * Get user's accepted tasks count
	 * 
	 * @param  int $userId User's ID
	 * 
	 * @return ActiveRow   Results
	 */
	public function getAcceptedUserTasksCount($userId)
	{
		return $this->accepted_taskRepository->getAcceptedByUser($userId)->count();
	}


	/**
	 * Get user's finished tasks
	 * 
	 * @param  int $userId User's ID
	 * 
	 * @return ActiveRow   Results
	 */
	public function getFinishedUserTasks($userId)
	{
		return $this->accepted_taskRepository->getFinishedByUser($userId);
	}


	/**
	 * Get user's satisfied tasks
	 * 
	 * @param  int $userId User's ID
	 * 
	 * @return ActiveRow   Results
	 */
	public function getSatisfiedUserTasks($userId)
	{
		return $this->accepted_taskRepository->getSatisfiedByUser($userId);
	}


	/**
	 * Get user's unsatisfied tasks
	 * 
	 * @param  int $userId User's ID
	 * 
	 * @return ActiveRow   Results
	 */
	public function getUnsatisfiedUserTasks($userId)
	{
		return $this->accepted_taskRepository->getUnsatisfiedByUser($userId);
	}

}