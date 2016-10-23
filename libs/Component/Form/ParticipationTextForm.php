<?php

namespace App\Component\Form;

use App\Model\Entity\ParticipationText;
use App\Model\Repository\Authors;
use App\Model\Repository\Competitions;
use Nette;

class ParticipationTextForm extends BaseForm
{
    /**
     * @var ParticipationText
     */
    protected $item;

    /**
     * @var Authors
     */
    protected $authors;

    /**
     * @var Competitions
     */
    protected $competitions;

    /**
     * Application form constructor.
     */
    public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL, Authors $authors = null, Competitions $competitions = null)
    {
        parent::__construct($parent, $name);

        $this->authors = $authors;
        $this->competitions = $competitions;
    }

    protected function _create()
    {
        $this->addText('name', 'Nazev');
        $this->addSelect('author', 'Autor', $this->loadFromRepo($this->authors, 'name'));
        $this->addTextArea('text', 'Soutěžní text', 50, 20);
        $this->addCheckbox('approved', 'Schvaleno');
    }

    protected function _loadItem()
    {
        $data['name']          = $this->item->name;
        $data['text']          = $this->item->text;
        $data['approved']      = $this->item->approved;
        if($this->item->user) {
            $data['author']        = $this->item->user->id;
        }

        parent::setValues($data);
    }

    protected function _process()
    {
        $this->item->name      = $this['name']->getValue();
        $this->item->text      = $this['text']->getValue();
        $this->item->approved  = $this['approved']->getValue();
        $this->item->new       = false;
        $this->item->user      = $this->authors->repository()->find($this['author']->getValue());
        $this->item->setCompetition($this->competitions->repository()->findOneById(7));
    }
}
