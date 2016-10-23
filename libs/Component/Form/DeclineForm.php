<?php

namespace App\Component\Form;

use App\Model\Entity\Participation;

class DeclineForm extends BaseForm
{
    /**
     * @var Participation
     */
    protected $item;

    protected function _create()
    {
        $this->addText('name', 'Nazev');
        $this->addTextArea('message', 'Komentar zamitnuti');
    }

    protected function _loadItem()
    {
        $data['name']          = $this->item->name;
        $data['deleted']       = $this->item->deleted;

        parent::setValues($data);
    }

    protected function _process()
    {
    }
}
