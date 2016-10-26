<?php

namespace App\Security;

use Nette\Object;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;

class Authenticator extends Object implements IAuthenticator
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * Authenticator constructor.
     * @param Container $container
     */
    public function __construct(\Nette\DI\Container $container)
    {
        $this->parameters = $container->getParameters();
    }

    /**
     * Performs an authentication against e.g. database.
     * and returns IIdentity on success or throws AuthenticationException
     * @param  array $credentials
     * @throws AuthenticationException
     * @return IIdentity
     */
    public function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;

        if (!isset($this->parameters['users'][$email])) {
            throw new AuthenticationException('User not found.');
        }

        if ($this->parameters['users'][$email] !== $password) {
            throw new AuthenticationException('Invalid password.');
        }

        return new Identity($email, ['administrator']);
    }
}
