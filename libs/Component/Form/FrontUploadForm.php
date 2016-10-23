<?php

namespace App\Component\Form;

use App\Model\Entity\Participation;
use Nette\Application\UI\Form;

class FrontUploadForm extends BaseForm
{
    /**
     * @var Participation
     */
    protected $item;

    protected function _create()
    {
        $this->addText('name', 'Nazev')
            ->setRequired(true);
        $this->addUpload('image', 'Foto')
            ->addRule(Form::IMAGE)
            ->setRequired(true);
        $this->addCheckbox('rules', 'Souhlas s pravidly soutěže')
            ->setRequired(true);
    }

    protected function _loadItem()
    {
    }

    protected function _process()
    {
        $this->item->name      = $this['name']->getValue();
    }
}
