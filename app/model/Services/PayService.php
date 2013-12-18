<?php
namespace Model\Services;

use Nette,
	Nette\Utils\Validators,
	Nette\Database\Table\ActiveRow;
use	Model\Repositories;

class PayService extends Nette\Object
{

	/** @var UserRepository */
	private $userRepository;
	
	/** @var IncomeRepository */
	private $incomeRepository;

	/** @var Repositories\WalletRepository */
	private $walletRepository;
	
	/** @var Repositories\BudgetRepository */
	private $budgetRepository;

	/** @var fees [injected] */
	public $fees;



	public function __construct(
		Repositories\UserRepository $userRepository,
		Repositories\IncomeRepository $incomeRepository,
		Repositories\WalletRepository $walletRepository,		
		Repositories\BudgetRepository $budgetRepository
	) {
		$this->userRepository 	 = $userRepository;
		$this->incomeRepository  = $incomeRepository;
		$this->walletRepository  = $walletRepository;
		$this->budgetRepository  = $budgetRepository;
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
		$wallet  = $this->walletRepository->get(array('user_id' => $userId));
		$balance = $wallet->balance + $amount;

		return $this->walletRepository->update($wallet, array('balance' => $balance));
	}



	/**
	 * Reserve budget from user's wallet for a given task
	 * 
	 * @param  Model\Domains\Task $task
	 * @param  int $userId
	 * @param  array $form
	 * 
	 * @return [type]         [description]
	 */
	public function createBudget($task, $userId, $form)
	{
		Validators::assert($userId, 'int');
		Validators::assert($task, 'Model\Domains\Task');

		$wallet  = $this->walletRepository->get(array('user_id' => $userId));
		$balance = $wallet->balance - $this->getOverallCosts($form);

		if ($balance < 0) {
			throw new \RuntimeException("Insuficient credit in your wallet", 1);
		}

		$budget = $this->recordBudget($task, $wallet->id, $form);
		$this->createIncomeFee($budget);

		return $this->walletRepository->update($wallet, array(
			'balance' => $balance
		));
	}



	/**
	 * Update budget from user's wallet for a given task
	 * 
	 * @param  Model\Domains\Task $task
	 * @param  int $userId
	 * @param  array $form
	 * 
	 * @return [type]         [description]
	 */
	public function updateBudget($task, $userId, $form)
	{
		Validators::assert($userId, 'int');
		Validators::assert($task, 'Model\Domains\Task');

		// Clear current budget and refund credit to user (without fee for creating task)
		$this->refundBudget($task, $userId);

		// Check credit in user's wallet
		$wallet  = $this->walletRepository->get(array('user_id' => $userId));
		$balance = $wallet->balance - $this->getOverallCosts($form) + $this->fees['fix'];
		if ($balance < 0) {
			throw new \RuntimeException("Insuficient credit in your wallet", 1);
		}

		// Reserve budget for current task
		$task->related('budget')->update(array(
			'wallet_id' 	=> $wallet->id,
			'budget' 		=> $this->getNettoCosts($form),
			'commission' 	=> $this->getCommission($form),
			// 'promotion_fee' => $promotion_fee,
		));

		// And update credit in user's wallet
		return $this->walletRepository->update($wallet, array(
			'balance' => $balance
		));
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
		// Get user's wallet and current budget for the task
		$wallet  		= $this->walletRepository->get(array('user_id' => $userId));
		$currentBudget 	= $this->getBudget($task);

		// Calculate new wallet balance after refunding current costs
		$newBalance = $wallet->balance;
		$newBalance += $currentBudget->budget;
		$newBalance += $currentBudget->commission; 
		$newBalance += $currentBudget->promotion_fee;

		// Clear current campaign costs (budget)
		$this->purgeBudget($task);

		// Return budget to the user's wallet
		$this->walletRepository->update($wallet, array(
			'balance' => $newBalance
		));
	}



	/**
	 * Netto price of the campaign: number of workers to be paid *(times) salary per worker
	 * 
	 * @param  array $values Form values
	 * 
	 * @return int           Netto price of the campaign
	 */
	private function getNettoCosts($values)
	{
		switch ($values['budget_type']) {
			case '1': 
				// Pay the best
				$budget = $values['salary'];
				break;
			
			case '2': 
				// Pay the best 10
				$budget = 10 * $values['salary'];

			default: 
				// Pay all
				$budget = $values['workers'] * $values['salary'];
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
	private function getCommission($form)
	{
		return $this->getNettoCosts($form) * ( $this->fees['commission'] - 1 );
	}



	/**
	 * Calclate the overall costs for the campaign = job price + commission + fees
	 * 
	 * @param  array $form 	Submitted form values
	 * 
	 * @return int        	Final budget
	 */
	private function getOverallCosts($form)
	{	
		return $this->getNettoCosts($form) * $this->fees['commission'] + $this->fees['fix'];
	}



	/**
	 * Record budget to the database
	 * 
	 * @param  Model\Domains\Task 	$task
	 * @param  int 					$wallet
	 * @param  array 				$form
	 * 
	 * @return ActiveRow
	 */
	private function recordBudget($task, $walletId, $form)
	{

		return $task->related('budget')->insert(array(
			'wallet_id' 	=> $walletId,
			'fee' 			=> $this->fees['fix'],
			'budget' 		=> $this->getNettoCosts($form),
			'commission' 	=> $this->getCommission($form),
			// 'promotion_fee' => $promotion_fee,
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
			'from' 		=> $budget->id,
			'type' 		=> 1,
			'amount' 	=> $budget->fee
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
			'budget'	 	=> 0.00,
			'commission' 	=> 0.00,
			'promotion_fee' => NULL
		));
	}

}
