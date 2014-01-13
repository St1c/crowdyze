<?php
namespace Model\Services;

use Nette,
	Nette\Security\AuthenticationException,
	Nette\Database\Table\ActiveRow,
	Nette\Http\FileUpload,
	Nette\Utils\Strings,
	Nette\Utils\Validators;
use	Model\Repositories,
	Model\Services,
	Utilities;

class UserService extends Nette\Object
{

	/** @var userRepository */
	private $userRepository;
	
	/** @var accepted_taskRepository */
	private $accepted_taskRepository;

	/** @var fileManager */
	private $fileManager;
	
	/** @var Utilities\MailerService */
	protected $mailerService;
	


	public function __construct(
			Repositories\UserRepository $userRepository,
			Repositories\Accepted_taskRepository $accepted_taskRepository,
			Services\FileManager $fileManager,
			Utilities\MailerService $mailerService)
	{
		$this->userRepository = $userRepository;
		$this->accepted_taskRepository = $accepted_taskRepository;
		$this->fileManager = $fileManager;
		$this->mailerService = $mailerService;
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
		if (isset($data['profile_photo'])) {
			$data['profile_photo'] = $this->fileManager->saveFileUpload('users', (string)$user->id, $data['profile_photo']);
		}
		return $user->update($data);
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
	 * Get user's pending tasks
	 * 
	 * @param  int $userId User's ID
	 * 
	 * @return ActiveRow   Results
	 */
	public function getPendingUserTasks($userId)
	{
		return $this->accepted_taskRepository->getPendingByUser($userId);
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



	/**
	 * Check whether the task is assigned to user
	 * 
	 * @param  int  $userId
	 * @param  int  $taskId
	 * 
	 * @return boolean TRUE|FALSE
	 */
	public function isAcceptedFilterByStatus($taskId, $userId)
	{
		$repo = $this->accepted_taskRepository;
		return $this->accepted_taskRepository->isAcceptedFilterByStatus($taskId, $userId, $repo::STATUS_ACCEPTED);
	}



	/**
	 * Check whether the task is assigned to user
	 * 
	 * @param  int  $userId
	 * @param  int  $taskId
	 * @param  int  $status 1=accepted|2=pending|3=satisfied|4=unsatisfied
	 * 
	 * @return boolean TRUE|FALSE
	 */
	public function isPendingFilterByStatus($taskId, $userId)
	{
		$repo = $this->accepted_taskRepository;
		return $this->accepted_taskRepository->isAcceptedFilterByStatus($taskId, $userId, $repo::STATUS_PENDING);
	}

}
