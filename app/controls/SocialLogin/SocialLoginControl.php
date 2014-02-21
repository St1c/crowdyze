<?php
namespace Controls;

use	Nette\Application\UI\Control;

class SocialLoginControl extends BaseControl
{
	/** @var \Facebook @inject */
	public $facebook;
	/** @var \Google @inject */
	public $google;

	/**
	 * Redirect to Facebook login URL
	 */
	public function handleFacebook()
	{
		$fbUrl = $this->facebook->getLoginUrl( array(
					'scope' 		=> $this->presenter->context->parameters['facebook']['scope'],
					'redirect_uri' 	=> $this->link('//fbLogin'), //Absolute path
		));
		$this->presenter->redirectUrl($fbUrl);
	}

	/**
	 * Perform login with Facebook credentials
	 */
	public function handleFbLogin()
	{
		$userProfile = $this->facebook->api('/me', 'GET');

		try {
			$this->performLogin('facebook', $userProfile);
		} catch ( Nette\Security\AuthenticationException $e) {  	// Authentication Error
			$this->presenter->flashMessage($e->getMessage(),'alert-error');
			$this->presenter->redirect('Signup:');
		}
	}

	/**
	 * Handle Google Login
	 */
	public function handleGoogle()
	{
		$url = $this->google->getLoginUrl(array(
			'scope' 		=> $this->presenter->context->parameters['google']['scope'],
			'redirect_uri' 	=> $this->link('//googleLogin'),
		));
		$this->presenter->redirectUrl($url);
	}

	/**
	 * Perform login with Google+
	 */
	public function handleGoogleLogin()
	{
		$userProfile = $this->getGoogleUserProfile();

		try {
			$this->performLogin('google', $userProfile);
		} catch ( Nette\Security\AuthenticationException $e) {  	// Authentication Error
			$this->presenter->flashMessage($e->getMessage(),'alert-error');
			$this->presenter->redirect('Signup:');
		}
	}

	/**
	 * @var string login type|facebook, google
	 * @var array social API data
	 */
	private function performLogin($type, $loginData)
	{
			// Try to authenticate the user with profile info			
			$this->presenter->getUser()->setExpiration('+15 days', FALSE);
			$this->presenter->getUser()->login($type, $loginData);

			// Authentication successful, login in!
			$this->presenter->flashMessage("login.flashes.social-login-success", 'alert-success', NULL, array( 'type' => ucfirst($type) ));
			$this->presenter->redirect(':Task:');
	}

	/**
	 * @return array Google+ user profile
	 */
	private function getGoogleUserProfile()
	{
		$code = $this->presenter->getParameter('code');
		$error = $this->presenter->getParameter('error');

		if( isset( $error ) ) {
			$this->presenter->flashMessage('login.flashes.google-permission');
			$this->presenter->redirect('Signup:');
		}

		$g 			 = $this->google;
		$token 		 = $g->getToken($code, $this->link('//googleLogin'));
		$userProfile = (array) $g->getInfo($token);

		return $userProfile;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '/../../templates/Controls/SocialLogin.latte');
		$this->template->render();
	}
}