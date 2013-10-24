<?php
namespace Model\Authenticators;

use Nette,
	Nette\Security,
	Model\Repositories\UsersRepository,
	Nette\Utils\Strings;

/**
 * Users authenticator.
 */
class PasswordAuthenticator extends BaseAuthenticator implements Nette\Security\IAuthenticator
{

	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $data)
	{
		$user = $this->usersService->find(array('email' => $data['email']));

		if (!$user) {
			throw new Security\AuthenticationException('User not found.', self::IDENTITY_NOT_FOUND);
		}

		if ( $user->password !== sha1($data['password']) ) {

			if ( empty($user->password) ) {
				throw new Security\AuthenticationException('Password has not been set! Please use social login on the left.', self::INVALID_CREDENTIAL);
			}

			throw new Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		return $this->usersService->createIdentity($user);	
	}

}
