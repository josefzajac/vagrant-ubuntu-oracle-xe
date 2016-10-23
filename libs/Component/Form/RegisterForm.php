<?php

namespace App\Component\Form;

use App\Model\Entity\User;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;

class RegisterForm extends BaseForm
{
    /**
     * @var User
     */
    protected $item;

    protected function _create()
    {
        $this->addText('name', 'Jmeno a prijmeni');
        $this->addText('reg_email', 'E-mail')
            ->setType('email')
            ->addRule(Form::EMAIL, 'Zadejte e-mail')
            ->setRequired(true);
        $this->addPassword('reg_password', 'password:')
            ->setType('password')
            ->setRequired(true);
    }

    protected function _loadItem()
    {
    }

    protected function _process()
    {
        $this->item->name      = $this['name']->getValue();
        $this->item->email     = $this['reg_email']->getValue();
        $this->item->password  = Passwords::hash($this['reg_password']->getValue());

        $this->item->fid       = '';
        $this->item->roles     = [User::REGISTERED];
    }
}
