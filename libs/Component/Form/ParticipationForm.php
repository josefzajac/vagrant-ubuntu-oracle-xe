<?php

namespace App\Component\Form;

use App\Model\Entity\Participation;

class ParticipationForm extends BaseForm
{
    /**
     * @var Participation
     */
    protected $item;

    protected function _create()
    {
        $this->addText('name', 'Nazev');
        $this->addCheckbox('approved', 'Schvaleno');
        $this->addCheckbox('new', 'Novinka');
        $this->addCheckbox('deleted', 'Schovat fotku');
    }

    protected function _loadItem()
    {
        $data['name']          = $this->item->name;
        $data['approved']      = $this->item->approved;
        $data['new']           = $this->item->new;
        $data['deleted']       = $this->item->deleted;

        parent::setValues($data);
    }

    protected function _process()
    {
        $this->item->name      = $this['name']->getValue();
        $this->item->approved  = $this['approved']->getValue();
        $this->item->new       = $this['new']->getValue();
        $this->item->deleted   = $this['deleted']->getValue();
    }
}
