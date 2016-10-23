<?php

namespace App\AdminModule\Presenters;

use App\Component\Form;
use App\Model\Repository\Users;

class UserPresenter extends AdminFormPresenter
{
    const ENTITY_NAME = 'App\Model\Entity\User';
    const FORM_NAME   = 'App\Component\Form\UserForm';

    /**
     * @var Users
     */
    protected $repo;

    protected function startup()
    {
        parent::startup();
    }

    public function actionDefault($page = 1, $limit = 10)
    {
        $this->template->total = $total = $this->repo->countBy();
        $this->template->limit = $limit;
        $this->template->all_items = $this->repo->repository()->findBy([], [], $limit, ($page-1) * $limit);
    }

    /**
     * @param Users $users
     */
    public function injectUsers(Users $users)
    {
        $this->repo = $users;
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
