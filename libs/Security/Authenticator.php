<?php

namespace App\Security;

use App\Model\Repository\Users;
use Nette\Object;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;

class Authenticator extends Object implements IAuthenticator
{
    /**
     * @var Users
     */
    private $users;

    /**
     * Authenticator constructor.
     * @param Users $users
     */
    public function __construct(Users $users)
    {
        $this->users = $users;
    }

    /**
     * Performs an authentication against e.g. database.
     * and returns IIdentity on success or throws AuthenticationException
     * @param  array                   $credentials
     * @throws AuthenticationException
     * @return IIdentity
     */
    public function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;

        $user = $this->users->findByEmail($email);

        if (!$user) {
            throw new AuthenticationException('User not found.');
        }

        if (!Passwords::verify($password, $user->password)) {
            throw new AuthenticationException('Invalid password.');
        }

        return new Identity($user->getId(), $user->getRoles(), $user->export());
    }
}
