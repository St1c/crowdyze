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

	/** @var fees */
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
	 * Netto price of the campaign: number of workers to be paid * salary per worker
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
	 * Transfer fees and commissions to Crowdyze account
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

}
