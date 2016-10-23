<?php

namespace App\Component\Form;

use App\Model\Entity\ParticipationText;
use Nette\Application\UI\Form;

class FrontUploadTextForm extends BaseForm
{
    /**
     * @var ParticipationText
     */
    protected $item;

    protected function _create()
    {
        $this->addText('name', 'Název příspěvku')
            ->setRequired(true);
        $this->addTextArea('text', 'Text příspěvku', 50, 15);
        $this->addUpload('image', 'Word / PDF / scan / foto');
        $this->addCheckbox('rules', 'Souhlas s pravidly soutěže')
            ->setRequired(true);
    }

    protected function _loadItem()
    {
    }

    protected function _process()
    {
        $this->item->name      = $this['name']->getValue();
        $this->item->text      = $this['text']->getValue();
    }
}
