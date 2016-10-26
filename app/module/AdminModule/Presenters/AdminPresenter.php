<?php

namespace App\AdminModule\Presenters;

use App\Presenters\BasePresenter;
use App\Model\ModelStorage;
use Nette;

abstract class AdminPresenter extends BasePresenter
{
    /** @var \Dibi\Connection @inject */
    public $db;

    protected function startup()
    {
        parent::startup();

        if ($this->getParameter('callback')) {
            \Tracy\Debugger::enable(\Tracy\Debugger::DEBUG);
        }

        ModelStorage::$db = $this->db;
        $this->template->lastVersion = 1;


//        $panel = new \Dibi\Bridges\Tracy\Panel;
//        $panel->register($this->db);


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

    public function actionLogin($login, $password)
    {
        $success = true;
        try {
            $this->getUser()->login($login, $password);
        } catch (Nette\Security\AuthenticationException $e) {
            $success = false;
        }

        $user = $this->getUser();

        echo json_encode(
            [
                'success' => $success && $user->isLoggedIn() && $user->isAllowed('Admin'),
            ]
        );
        $this->terminate();
    }

    public function actionLogout()
    {
        $this->getUser()->logout(true);

        echo json_encode(
            [
                'success' => true,
            ]
        );
        $this->terminate();
    }

    public function actionCheckUser()
    {
        $user = $this->getUser();

        echo json_encode(
            [
                'success' => $user->isLoggedIn() && $user->isAllowed('Admin'),
            ]
        );
        $this->terminate();
    }
}
