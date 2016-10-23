<?php

namespace App\AdminModule\Presenters;

use App\Presenters\BasePresenter;

abstract class AdminPresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();

        $user = $this->getUser();
//        if (!$user->isLoggedIn()) {
//            $this->redirect(':Frontend:Login:login', ['return_url' => $this->getHttpRequest()->getUrl()->getAbsoluteUrl()]);
//        }
//
//        if (!$user->isAllowed('Admin')) {
//            $this->flashMessage('tr.admin.access_not_granted', 'danger');
//            $this->redirect(':Frontend:Frontend:');
//        }
    }

    /**
     */
    protected function beforeRender()
    {
        parent::beforeRender();
        $this->template->new_participations = [];

        $this->template->active_competitions = [];
    }
}
