<?php
namespace Model\Services;

use Nette,
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
	
	/** @var Repositories\ReserveRepository */
	private $reserveRepository;
	


	public function __construct(
		Repositories\UserRepository $userRepository,
		Repositories\IncomeRepository $incomeRepository,
		Repositories\WalletRepository $walletRepository,		
		Repositories\ReserveRepository $reserveRepository
	) {
		$this->userRepository 	 = $userRepository;
		$this->incomeRepository  = $incomeRepository;
		$this->walletRepository  = $walletRepository;
		$this->reserveRepository = $reserveRepository;
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
		$balance = $wallet->balance + $amount;
		return $this->walletRepository->update($wallet, array('balance' => $balance));
	}


	public function reserveBudget($userId, $taskId, $amount)
	{
		Validators::assert($userId, 'int');
		Validators::assert($taskId, 'int');

		$wallet = $this->walletRepository->get(array('user_id' => $userId));
		$reserve = $this->reserveRepository->get(array('task_id' => $taskId));
		$balance = $wallet->balance - $amount;

		if ($balance < 0) {
			throw new \RuntimeException("Insuficient credit in your wallet", 1);
		}

		return $this->walletRepository->update($wallet, array(
			'balance' => $balance
		));
	}

}
