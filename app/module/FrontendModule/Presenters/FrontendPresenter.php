<?php

namespace App\FrontendModule\Presenters;

use App\Model\Entity\Competition;

class FrontendPresenter extends LoginPresenter
{
    public function startup()
    {
        $this->template->competitions = [];
        $this->template->isHome       = false;

        parent::startup();
    }

    public function renderDefault()
    {
        $this->template->isHome = true;
    }

    public function renderCurrent()
    {
        foreach ($this->competitions->repository()->findByEnabled(1) as $competition) {
            if ($competition->getStatus() == Competition::STATUS_ACTIVE) {
                $this->template->competitions[] = $competition;
            }
        }
    }

    public function renderNext()
    {
        foreach ($this->competitions->repository()->findByEnabled(1) as $competition) {
            if ($competition->getStatus() == Competition::STATUS_PENDING) {
                $this->template->competitions[] = $competition;
            }
        }
    }

    public function renderOld()
    {
        foreach ($this->competitions->repository()->findByEnabled(1) as $competition) {
            if ($competition->getStatus() == Competition::STATUS_FINISHED) {
                $this->template->competitions[] = $competition;
            }
        }
    }

}
