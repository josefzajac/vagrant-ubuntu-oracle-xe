<?php

namespace App\AdminModule\Presenters;

use App\Presenters\BasePresenter;
use App\Model\ModelStorage;
use Nette;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Tracy\Dumper;

abstract class AdminPresenter extends BasePresenter
{
    /** @var \Dibi\Connection @inject */
    public $db;

    protected function startup()
    {
        parent::startup();

        if (!$this->getUser()->isAllowed('Admin') && ! in_array($this->action, ['loginpage', 'login'])) {
            $this->flashMessage('tr.admin.access_not_granted', 'danger');
            $this->redirect('Homepage:loginpage', ['return_url' => $this->link('this')]);
        }

        ModelStorage::$db = $this->db;

        $this->template->model = $this->getParameter('model');
        $this->template->menuEntities = $this->menuEntities;
        $this->template->lastVersion = 1;
    }


    protected function createComponentLoginForm()
    {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer);
        $form->addText('login', 'login')
            ->setRequired(true);
        $form->addPassword('password', 'password:')
            ->setType('password')
            ->setRequired(true);
        $form->addHidden('return_url', $this->getParameter('return_url'));
        $form->addSubmit('signin', 'Sign in');
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];

        return $form;
    }

    public function loginFormSucceeded(Form $form = null, $values)
    {
        $message = 'tr.user.success';
        $success = true;
        try{
            $this->getUser()->login($values->login, $values->password);
        } catch (Nette\Security\AuthenticationException $e) {
            $success = false;
            switch($e->getMessage()) {
                case 'User not found.'   : $message = 'tr.user.not_found'; break;
                case 'Invalid password.' : $message = 'tr.user.wrong_pass'; break;
                default: $message = 'tr.user.error'; break;
            }
        }
        $this->flashMessage($message, $success ? 'info' : 'danger');

        if ($returnUrl = $form && isset($form['return_url']) ? $form['return_url']->getValue() : null) {
            $this->redirectUrl($returnUrl);
        } else {
            $this->redirect('this');
        }
    }

    /**
     * @param  Nette\Application\IResponse
     * @return void
     */
    protected function shutdown($response)
    {
        foreach(range(1,10) as $i) {
            if(!$h = $this->getHttpResponse()->getHeader('X-Wf-dibi-1-1-d'.$i)) continue;
            $h = json_decode(substr($h, 1, strlen($h)-2));
            if (!isset($h[1])) continue;

            foreach($h[1] as $r) {
                \Tracy\Debugger::barDump($r[1], null, [Dumper::TRUNCATE => 1000]);
            }
        }
    }
}
