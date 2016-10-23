<?php

namespace App\Component\Form;

use App\Model\Entity\User;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;

class RecoveryForm extends BaseForm
{
    /**
     * @var User
     */
    protected $item;

    protected function _create()
    {
        $this->addPassword('reg_password', 'password:')
            ->setType('password')
            ->setRequired(true);
    }

    protected function _loadItem()
    {
    }

    protected function _process()
    {
        $this->item->password  = Passwords::hash($this['reg_password']->getValue());
    }
}
