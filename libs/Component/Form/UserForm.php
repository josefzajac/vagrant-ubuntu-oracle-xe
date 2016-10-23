<?php

namespace App\Component\Form;

use App\Model\Entity\User;
use Nette\Application\UI\Form;

class UserForm extends BaseForm
{
    /**
     * @var User
     */
    protected $item;

    protected function _create()
    {
        $this->addText('name', 'Name');
        $this->addText('email', 'E-mail');
    }

    protected function _loadItem()
    {
        $data['name']      = $this->item->name;
        $data['email']     = $this->item->email;

        parent::setValues($data);
    }

    protected function _process()
    {
        $this->item->name      = $this['name']->getValue();
        $this->item->email     = $this['email']->getValue();
    }
}
