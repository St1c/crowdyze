<?php
namespace Controls;

use	Nette\Application\UI\Control,
	Model\Services\UserService,
	PayPal\Rest\ApiContext,
	PayPal\Auth\OAuthTokenCredential,
	PayPal\Api\Amount,
	PayPal\Api\Details,
	PayPal\Api\Item,
	PayPal\Api\ItemList,
	PayPal\Api\Payer,
	PayPal\Api\Payment,
	PayPal\Api\RedirectUrls,
	PayPal\Api\Transaction,
	PayPal\Api\ExecutePayment,
	PayPal\Api\PaymentExecution;


class PaypalControl extends BaseControl
{

	/** @var Paypal neon settings */
	public $paypal;

	/** @var Model\Services\UserService @inject */
	public $userService;



	/**
	 * Redirect to Facebook login URL
	 */
	public function handleDeposit()
	{

		$apiContext = $this->getApiContext();
		$payment = $this->createPayment();

		try {
			$payment->create($apiContext);
		} catch (PayPal\Exception\PPConnectionException $ex) {
			$this->presenter->flashMessage("Exception: " . $ex->getMessage(), 'alert-danger');
			$this->presenter->redirect('Wallet:deposit');
		}

		$this->redirectToPaypal($payment);
	}



	public function handleExecutePayment($success)
	{

		$paypalSession 	= $this->presenter->getSession('paypal');
		
		if ( isset($success) && $success == 'true' ) {
			$paymentId 		= $paypalSession->paymentId;
			$apiContext 	= $this->getApiContext();

			$payment = Payment::get($paymentId, $apiContext);
			$execution = new PaymentExecution();
			$execution->setPayer_id($this->presenter->getParameter('PayerID'));
			$result = $payment->execute($execution, $apiContext);

			$this->userService->addBalance($this->presenter->user->id, $paypalSession->result->transactions[0]->amount->details->subtotal);

			$this->presenter->flashMessage('Credit added', 'alert-success');
			$this->presenter->redirect('Wallet:deposit');

		} else {
			$this->presenter->flashMessage('Cancelled payment', 'alert-danger');
			$this->presenter->redirect('Wallet:deposit');
		}

	}


	private function getApiContext() 
	{
		// ### Api context
		// Use an ApiContext object to authenticate API calls. The clientId and clientSecret for the 
		// OAuthTokenCredential class can be retrieved from developer.paypal.com

		$apiContext = new ApiContext(
						new OAuthTokenCredential(
								$this->paypal['clientId'],
								$this->paypal['secret']
							)
					);

		// #### SDK configuration
		$apiContext->setConfig( array( 
						'mode' => $this->paypal['mode'],
					)
				);

		return $apiContext;
	}


	private function createPayment()
	{

		$payer = new Payer();
		$payer->setPayment_method("paypal");


		$item1 = new Item();
		$item1->setName('Credit for crowdyze.me')
			->setCurrency('USD')
			->setQuantity(1)
			->setPrice('50.00');

		$itemList = new ItemList();
		$itemList->setItems(array($item1));

		$details = new Details();
		$details->setTax('10.00');
		$details->setSubtotal('50.00');

		$amount = new Amount();
		$amount->setCurrency('USD');
		$amount->setTotal('60.00');
		$amount->setDetails($details);

		$transaction = new Transaction();
		$transaction->setAmount($amount);
		$transaction->setItemList($itemList);
		$transaction->setDescription("Credit for crowdyze.me");

		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturn_url($this->link('//executePayment', array('success' => 'true')));
		$redirectUrls->setCancel_url($this->link('//executePayment', array('success' => 'false')));

		$payment = new Payment();
		$payment->setIntent("sale");
		$payment->setPayer($payer);
		$payment->setRedirect_urls($redirectUrls);
		$payment->setTransactions(array($transaction));

		return $payment;
	}



	private function redirectToPaypal($payment)
	{
		foreach($payment->getLinks() as $link) {
			if($link->getRel() == 'approval_url') {
				$redirectUrl = $link->getHref();
				break;
			}
		}

		$paypalSession 				= $this->presenter->getSession('paypal');
		$paypalSession->paymentId 	= $payment->getId();

		if(isset($redirectUrl)) {
			$this->presenter->redirectUrl($redirectUrl);
		}
	}



	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/Paypal.latte');
		$this->template->render();
	}
}