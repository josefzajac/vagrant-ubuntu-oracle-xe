<?php

namespace App\AdminModule\Presenters;

use App\Component\Form;
use App\Model\Entity\TariffSpace;
use App\Model\Repository\TariffSpaces;

class TariffSpacePresenter extends AdminFormPresenter
{
    const ENTITY_NAME = 'App\Model\Entity\TariffSpace';
    const FORM_NAME   = 'App\Component\Form\TariffSpace';

    /**
     * @var TariffSpaces
     */
    protected $tariffSpaces;

    protected function startup()
    {
        parent::startup();
        $this->repo = $this->tariffSpaces;
    }

    protected function getEntityName()
    {
        return self::ENTITY_NAME;
    }

    protected function getFormName()
    {
        return self::FORM_NAME;
    }

    /**
     * @param TariffSpaces $repo
     */
    public function injectTariffSpaces(TariffSpaces $repo)
    {
        $this->tariffSpaces = $repo;
    }
}
