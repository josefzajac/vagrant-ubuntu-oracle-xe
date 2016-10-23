<?php

namespace App\Component\Form;

use App\Model\Entity\Author;
use App\Model\Entity\User;
use Nette\Application\UI\Form;
use Nette\Security\Passwords;

class AdminAuthorForm extends BaseForm
{
    /**
     * @var Author
     */
    protected $item;

    protected function _create()
    {
        $this->addText('name', 'Jméno a příjmeni')->setRequired(true);
        $this->addText('nick', 'Přezdívka');

        $this->addText('age', 'Vek')->setRequired(true);
        $this->addText('phone', 'Telefon na rodiče');
        $this->addText('parent_email', 'E-mail rodiče')
            ->setType('email');
        $this->addText('school_name', 'Jméno školy');
        $this->addText('school_street', 'Škola (ulice)');
        $this->addText('school_city', 'Škola (město)');
    }

    protected function _loadItem()
    {
        $data['name']          = $this->item->getName();
        $data['nick']          = $this->item->getNick();
        $data['age']           = $this->item->getAge();
        $data['phone']         = $this->item->getPhone();
        $data['parent_email']  = $this->item->getParentEmail();
        $data['school_name']   = $this->item->getSchoolName();
        $data['school_street'] = $this->item->getSchoolStreet();
        $data['school_city']   = $this->item->getSchoolCity();

        parent::setValues($data);
    }

    protected function _process()
    {
        $this->item->name         = $this['name']->getValue();
        $this->item->nick         = $this['nick']->getValue();
        if (!$this->item->id) {
            $this->item->email        = 'test'.rand(10000, 100000).'@p3.dev';
            $this->item->password     = Passwords::hash('pass'.rand(10000, 100000));
        }

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
