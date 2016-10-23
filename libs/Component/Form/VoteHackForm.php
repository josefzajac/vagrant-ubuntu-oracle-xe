<?php

namespace App\Component\Form;

use App\Model\Entity\Participation;

class VoteHackForm extends BaseForm
{
    /**
     * @var Participation
     */
    protected $item;

    protected function _create()
    {
        $this->addText('value', 'Kolik hlasu?');
    }

    protected function _loadItem()
    {
        $data = [];
        parent::setValues($data);
    }

    protected function _process()
    {
    }
}
