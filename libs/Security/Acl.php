<?php

namespace App\Security;

use App\Model\Entity\User;
use Nette\Security\Permission;

class Acl extends Permission
{
    /**
     * Acl constructor.
     */
    public function __construct()
    {
        $this->defineRoles();
        $this->defineResources();
        $this->definePrivileges();
    }

    private function defineRoles()
    {
        $this->addRole('guest');
        $this->addRole(User::REGISTERED, 'guest');
        $this->addRole(User::AUTHOR, User::REGISTERED);
        $this->addRole(User::ADMIN, User::AUTHOR);
    }

    private function defineResources()
    {
        $this->addResource('Admin');
    }

    private function definePrivileges()
    {
        $this->allow(User::ADMIN, 'Admin');
    }
}
