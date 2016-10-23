<?php

namespace App\Component\Form;

use App\Model\Entity\Author;
use App\Model\Entity\User;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;

class AuthorForm extends BaseForm
{
    /**
     * @var Author
     */
    protected $item;

    protected function _create()
    {
        $this->addText('name', 'Jméno a příjmeni')->setRequired(true);
        $this->addText('nick', 'Přezdívka')->setRequired(true);
        $this->addText('reg_author_email', 'E-mail')->setRequired(true)
            ->addRule(Form::EMAIL, 'Zadejte e-mail')
            ->setType('email');
        $this->addPassword('reg_author_password', 'password:')
            ->setType('password')
            ->setRequired(true);
        $this->addText('age', 'Vek')->setRequired(true);
        $this->addText('phone', 'Telefon na rodiče')->setRequired(true);
        $this->addText('parent_email', 'E-mail rodiče')->setRequired(true);
        $this->addText('school_name', 'Jméno školy')->setRequired(true);
        $this->addText('school_street', 'Škola (ulice)')->setRequired(true);
        $this->addText('school_city', 'Škola (město)')->setRequired(true);
    }

    protected function _loadItem()
    {
        parent::setValues([]);
    }

    protected function _process()
    {
        $this->item->name         = $this['name']->getValue();
        $this->item->nick         = $this['nick']->getValue();
        $this->item->email        = $this['reg_author_email']->getValue();
        $this->item->password     = Passwords::hash($this['reg_author_password']->getValue());
        $this->item->age          = $this['age']->getValue();
        $this->item->phone        = $this['phone']->getValue();
        $this->item->parentEmail  = $this['parent_email']->getValue();
        $this->item->schoolName   = $this['school_name']->getValue();
        $this->item->schoolStreet = $this['school_street']->getValue();
        $this->item->schoolCity   = $this['school_city']->getValue();

        $this->item->fid       = '';
        $this->item->roles     = [User::REGISTERED, User::AUTHOR];
    }
}
