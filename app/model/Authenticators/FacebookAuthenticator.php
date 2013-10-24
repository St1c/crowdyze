<?php
namespace Model\Authenticators;

use Nette,
	Model\Repositories\UsersRepository;

class FacebookAuthenticator extends BaseAuthenticator implements Nette\Security\IAuthenticator 
{

	/**
	 * @param array $me Facebook response array
	 * @return \Nette\Security\Identity
	 */
	public function authenticate(array $me)
	{
		$user = $this->usersService->find( array('email' => $me['email']) );

		if (!$user) {
			// Record new user
			$user = $this->usersService->register('facebook', $me);
			
		} else {
			// Update user
			$this->usersService->updateFromSocial('facebook', $user, $me);
		}

		return $this->usersService->createIdentity($user);
	}

}
