<?php
namespace Model\Authenticators;

use Nette;

class GoogleAuthenticator extends BaseAuthenticator implements Nette\Security\IAuthenticator
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
			$user = $this->signService->register('google', $me);
		
		} else {
			// Update user
			$this->signService->updateFromSocial('google', $user, $me);
		}

		return $this->signService->createIdentity($user);
	}

}