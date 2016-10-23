<?php

namespace App\AdminModule\Presenters;

use App\Component\Form;
use App\Model\Repository\Authors;

class AuthorPresenter extends AdminFormPresenter
{
    const ENTITY_NAME = 'App\Model\Entity\Author';
    const FORM_NAME   = 'App\Component\Form\AdminAuthorForm';

    /**
     * @var Authors
     */
    protected $repo;

    protected function startup()
    {
        parent::startup();
        $this->repo = $this->authors;
    }

    protected function getEntityName()
    {
        return self::ENTITY_NAME;
    }

    protected function getFormName()
    {
        return self::FORM_NAME;
    }
}
