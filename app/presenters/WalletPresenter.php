<?php 

namespace App;

use Nette;

class WalletPresenter extends BaseSignedPresenter 
{

	/** @var Controls\IPaypalControlFactory @inject */
	public $paypalControlFactory;

	/** @var Model\Services\UserService @inject */
	public $userService;

	public function createComponentPaypal()
	{
		return $this->paypalControlFactory->create();
	}


	/**
	 * Add money to user wallet
	 */
	public function actionDeposit()
	{

	}



	/**
	 * Add money to user wallet
	 */
	public function actionWithdraw()
	{

	}

}
