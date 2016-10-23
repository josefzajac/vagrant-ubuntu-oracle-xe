<?php

namespace App\Component\Form;

use App\Model\Entity\ParticipationText;

class DeclineTextForm extends BaseForm
{
    /**
     * @var ParticipationText
     */
    protected $item;

    protected function _create()
    {
        $this->addText('name', 'Nazev');
        $this->addTextArea('text', 'Soutěžní text', 50, 20);
        $this->addTextArea('message', 'Komentář zamítnutí');
    }

    protected function _loadItem()
    {
        $data['name']          = $this->item->name;
        $data['text']          = $this->item->text;
        $data['deleted']       = $this->item->deleted;

        parent::setValues($data);
    }

    protected function _process()
    {
    }
}
