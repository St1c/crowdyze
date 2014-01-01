<?php
namespace Model\Services;

use Nette,
	Nette\Utils\Validators,
	Nette\Database\Table\ActiveRow;
use	Model\Repositories,
	Model\Domains\Task;

class PayService extends Nette\Object
{

	/** @var UserRepository */
	private $userRepository;
	
	/** @var IncomeRepository */
	private $incomeRepository;
	
	/** @var Repositories\BudgetRepository */
	private $budgetRepository;

	/** @var fees [injected] */
	public $fees;



	public function __construct(
		Repositories\UserRepository $userRepository,
		Repositories\IncomeRepository $incomeRepository,
		Repositories\BudgetRepository $budgetRepository
	) {
		$this->userRepository 	 = $userRepository;
		$this->incomeRepository  = $incomeRepository;
		$this->budgetRepository  = $budgetRepository;
	}



	/**
	 * Get user cash balance
	 * 
	 * @param  int $userId
	 * 
	 * @return string 
	 */
	public function getWallet($userId)
	{
		return $this->userRepository->getWallet($userId);
	}



	/**
	 * Add credit to the user's account
	 * 
	 * @param int $userId 
	 * @param int $amount 
	 *
	 * @return ActiveRow
	 */
	public function addWallet($userId, $amount)
	{	
		$wallet = $this->getWallet($userId);
		return $this->userRepository->updateWallet($userId, $wallet + $amount);
	}



	/**
	 * Reserve budget from user's wallet for a given task, when creating new task
	 * 
	 * @param  Model\Domains\Task $task
	 * @param  int $userId
	 * 
	 * @return [type]         [description]
	 */
	public function createBudget($task, $userId)
	{
		Validators::assert($userId, 'int');
		Validators::assert($task, 'Model\Domains\Task');

		$balance = $this->getWallet($userId) - $this->getOverallCosts($task);

		if ($balance < 0) {
			throw new \RuntimeException("notice.exception.insuficient_credit", 1);
		}

		$budget = $this->recordBudget($task, $userId);
		$this->createIncomeFee($budget);

		// And update credit in user's wallet
		return $this->userRepository->updateWallet($userId, $balance);
	}



	/**
	 * Update budget from user's wallet for a given task, when editing existing task
	 * 
	 * @param  Model\Domains\Task $task
	 * @param  int $userId
	 * 
	 * @return [type]         [description]
	 */
	public function updateBudget($task, $userId)
	{
		Validators::assert($userId, 'int');
		Validators::assert($task, 'Model\Domains\Task');

		// Clear current budget and refund credit to user (without fee for creating task)
		$this->refundBudget($task, $userId);

		// Check credit in user's wallet
		$balance = $this->getWallet($userId) - $this->getOverallCosts($task) + $this->fees['fix'];
		if ($balance < 0) {
			throw new \RuntimeException("notice.exception.insuficient_credit", 1);
		}

		// Reserve budget for current task
		$task->related('budget')->update(array(
			'user_id' 		=> $userId,
			'budget' 		=> $this->getNettoCosts($task),
			'commission' 	=> $this->getCommission($task),
			'promotion'		=> $this->getPromotion($task)
		));

		// And update credit in user's wallet
		return $this->userRepository->updateWallet($userId, $balance);
	}



	/**
	 * Get current budget
	 * 
	 * @param  Model\Domains\Task 	$task
	 * 
	 * @return ActiveRow       		$budget
	 */	
	private function getBudget($task)
	{
		return $task->related('budget')->fetch();
	}



	/**
	 * Return budget of the campaign back to the user's wallet
	 * 
	 * @param  Model\Domains\Task 	$task
	 * @param  int 					$userId
	 */
	private function refundBudget($task, $userId)
	{
		// Get current budget for the task
		$currentBudget 	= $this->getBudget($task);

		// Calculate new wallet balance after refunding current costs
		$newBalance = $this->getWallet($userId);
		$newBalance += $currentBudget->budget;
		$newBalance += $currentBudget->commission; 
		$newBalance += $currentBudget->promotion;

		// Clear current campaign costs (budget)
		$this->purgeBudget($task);

		// Return budget to the user's wallet
		return $this->userRepository->updateWallet($userId, $newBalance);
	}



	/**
	 * Netto price of the campaign: number of workers to be paid *(times) salary per worker
	 * 
	 * @param  array $values Form values
	 * 
	 * @return int           Netto price of the campaign
	 */
	private function getNettoCosts(Task $task)
	{
		switch ($task->budgetType) {
			case 1: 
				// Pay the best
				$budget = $task->salary;
				break;
			
			case 2: 
				// Pay the best 10
				$budget = 10 * $task->salary;

			default: 
				// Pay all
				$budget = $task->workers * $task->salary;
				break;
		}

		return $budget;
	}



	/**
	 * Calculate commission based on the netto price of the campaign
	 * 
	 * @param  array $form 	Submitted form values
	 * 
	 * @return int        	Commission
	 */
	private function getCommission(Task $task)
	{
		return $this->getNettoCosts($task) * ( $this->fees['commission'] );
	}



	/**
	 * Get fees for promoting task
	 * 
	 * @param  Task   $task
	 * 
	 * @return int    Promotion fee
	 */
	private function getPromotion(Task $task)
	{
		if ($task->promotion > 0) {
			return $this->getNettoCosts($task) * ($this->fees['promotion'][$task->promotion - 1]);
		} else {
			return 0;
		}
	}	



	/**
	 * Calculate the overall costs for the campaign = job price + commission + fees
	 * 
	 * @param  array $form 	Submitted form values
	 * 
	 * @return int        	Final budget
	 */
	private function getOverallCosts(Task $task)
	{	
		return $this->getNettoCosts($task) + 
				$this->getCommission($task) + 
				$this->getPromotion($task) + 
				$this->fees['fix'];
	}



	/**
	 * Record budget to the database
	 * 
	 * @param  Model\Domains\Task 	$task
	 * @param  int 					$userId
	 * 
	 * @return ActiveRow
	 */
	private function recordBudget($task, $userId)
	{
		return $task->related('budget')->insert(array(
			'user_id'  	 => $userId,
			'fee' 		 => $this->fees['fix'],
			'budget' 	 => $this->getNettoCosts($task),
			'commission' => $this->getCommission($task),
			'promotion'	 => $this->getPromotion($task)
		));
	}



	/**
	 * Transfer fees and commissions to local Crowdyze account
	 * 
	 * @param  ActiveRow $budget
	 * 
	 * @return ActiveRow 
	 */
	private function createIncomeFee($budget)
	{
		return $this->incomeRepository->create(array(
			'from' 	=> $budget->id,
			'type' 	=> 1,
			'amount' => $budget->fee
		));
	}



	/**
	 * Clear all budget costs for a given task
	 * 
	 * @param  Models\Domains\Task $task
	 */
	private function purgeBudget($task)
	{
		$task->related('budget')->update(array(
			'budget'	 => 0.00,
			'commission' => 0.00,
			'promotion'	 => 0.00
		));
	}



	/**
	 * Process pay transcation
	 */
	public function doPayResult(Task $task, $userId)
	{
		Validators::assert($task->id, 'int');
		Validators::assert($userId, 'int');

		// Check if budget is sufficient to pay the user
		if (! $budget = $this->getBudget($task)) {
			throw new \LogicException('Budget for task: ' . $task->id . ' not found.');
		}

		$newBudget = $budget->budget - $task->salary;

		if ( $newBudget < 0 ) {
			throw new \RuntimeException("notice.exception.insuficient_credit", 1);
		}
		
		// Pay commission to Crowdyze account
		$resultCommission = $task->salary * ($this->fees['commission'] );
		$updateCommission = $budget->commission - $resultCommission;

		// Pay promotion fee to Crowdyze account
		if ($task->promotion > 0) {
			$resultPromotion = $task->salary * $this->fees['promotion'][$task->promotion - 1];
			$updatePromotion = $budget->promotion - $resultPromotion;
		}
		else {
			$updatePromotion = 0;
			$resultPromotion = 0;
		}

		$this->createIncomeCommission($budget, $resultCommission, 2);
		$this->createIncomeCommission($budget, $resultPromotion, 3);
		$task->related('budget')->update(array(
			'budget'	 => $newBudget,
			'commission' => $updateCommission,
			'promotion'	 => $updatePromotion
		));

		// Transfer salary to user's wallet
		$this->addWallet($userId, $task->salary);
	}



	/**
	 * Transfer commission to local Crowdyze account
	 * 
	 * @param  ActiveRow $budget
	 * @param  int 				Fee amount
	 * @param  int 				Type of Fee 
	 */
	private function createIncomeCommission($budget, $commission, $type)
	{
		$this->incomeRepository->create(array(
			'from' 		=> $budget->id,
			'type' 		=> $type,
			'amount' 	=> $commission
		));
	}


}
