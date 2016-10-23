<?php

namespace App\Component\Form;

use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapRenderer;

abstract class BaseForm extends Form
{
    protected $item;

    public function create()
    {
        $this->setRenderer(new BootstrapRenderer);

        $this->_create();

        $this->addHidden('id');
    }

    abstract protected function _create();

    public function setItem($item)
    {
        $this->item = $item;

        $data       = [];
        $data['id'] = $this->item->getId();
        parent::setValues($data);

        $this->_loadItem();
    }

    abstract protected function _loadItem();

    public function process($item)
    {
        $this->item = $item;

        $this->_process();
    }

    abstract protected function _process();

    protected function loadFromRepo(\App\Model\Repository\Repository $repo, $key)
    {
        return $repo->repository()->findPairs($key);
    }
}
