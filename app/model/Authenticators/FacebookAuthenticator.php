<?php
namespace Model\Authenticators;

use Nette;

class FacebookAuthenticator extends BaseAuthenticator implements Nette\Security\IAuthenticator 
{

	/**
	 * @param array $me Facebook response array
	 * @return \Nette\Security\Identity
	 */
	public function authenticate(array $me)
	{
		$user = $this->signService->find( array('email' => $me['email']) );

		if (!$user) {
			// Record new user
			$user = $this->signService->register('facebook', $me);
			
		} else {
			// Update user
			$this->signService->updateFromSocial('facebook', $user, $me);
		}

		return $this->signService->createIdentity($user);
	}

}
