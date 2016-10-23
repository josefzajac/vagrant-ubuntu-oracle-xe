<?php

namespace App\FrontendModule\Presenters;

use App\Component\Form\AuthorForm;
use App\Component\Form\RecoveryForm;
use App\Component\Form\RegisterForm;
use App\Model\Entity\Author;
use App\Model\Entity\User;
use App\Model\Repository\Users;
use App\Presenters\BasePresenter;
use Nette\Application\UI\Form;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Tomaj\Form\Renderer\BootstrapRenderer;


class LoginPresenter extends BasePresenter
{

    /**
     * @var \Nette\Caching\Cache
     */
    protected $cache;

    /**
     * @var Users
     */
    protected $users;

    protected function startup()
    {
        parent::startup();
        $this->template->userData = [];
    }

    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->userData = $this->getUser()->isLoggedIn() ? $this->getUser()->getIdentity()->getData() : [];
    }

    private function getUserKey(User $user)
    {
        return 'recovery_' . $user->getId() . '_' . $user->getEmail();
    }

    public function actionLogout()
    {
        $this->getUser()->logout(true);
        $this->flashMessage('tr.user.logout', 'info');
        $this->redirectUrl($this->getHttpRequest()->getReferer());
    }

    //-----------------------  LOGIN

    protected function createComponentLoginForm()
    {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer);
        $form->addText('email', 'email:')
            ->setType('email')
            ->addRule(Form::EMAIL, 'Zadejte e-mail')
            ->setRequired(true);
        $form->addPassword('password', 'password:')
            ->setType('password')
            ->setRequired(true);
        $form->addSubmit('login', $this->trans('tr.modal.login'));
        $form->addHidden('return_url', $this->getParameter('return_url'));
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];

        return $form;
    }

    public function loginFormSucceeded(Form $form = null, $values)
    {
        $message = 'tr.user.success';
        $success = true;
        try{
            $this->getUser()->login($values->email, $values->password);
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

    //-----------------------  REGISTER

    public function createComponentRegisterForm()
    {
        $form = new RegisterForm();
        $form->create();
        $form->addHidden('return_url', $this->getParameter('return_url'));
        $form->addSubmit('send', $this->translator->trans('tr.modal.register_button'))
            ->onClick[] = [$this, 'registerForm'];

        return $form;
    }

    public function registerForm(Nette\Forms\Controls\SubmitButton $button)
    {
        $user = new User();

        try {
            $button->getForm()->process($user);

            $this->users->repository()->save($user);

            $this->flashMessage('tr.user.user_registered');
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->flashMessage('tr.user.email_unique', 'danger');
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->flashMessage('tr.user.email_unique', 'danger');
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'danger');
        }

        $data    = $button->getForm()->getValues();
        $credentials = (object) ['email' => $data['reg_email'], 'password' => $data['reg_password']];

        $this->loginFormSucceeded($button->getForm(), $credentials);
    }

    //-----------------------  REGISTER

    public function createComponentRegisterAuthorForm()
    {
        $form = new AuthorForm();
        $form->create();
        $form->addHidden('return_url', $this->getParameter('return_url', $this->getHttpRequest()->getReferer()));
        $form->addSubmit('send', $this->translator->trans('tr.modal.register_button'))
            ->onClick[] = [$this, 'registerAuthorForm'];

        return $form;
    }

    public function registerAuthorForm(Nette\Forms\Controls\SubmitButton $button)
    {
        $user = new Author();

        try {
            $button->getForm()->process($user);

            $this->users->repository()->save($user);

            $this->flashMessage('tr.user.user_registered');
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->flashMessage('tr.user.email_unique', 'danger');
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->flashMessage('tr.user.email_unique', 'danger');
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'danger');
        }

        $data    = $button->getForm()->getValues();
        $credentials = (object) ['email' => $data['reg_author_email'], 'password' => $data['reg_author_password']];

        $this->loginFormSucceeded($button->getForm(), $credentials);
    }

    //-----------------------  PROFILE

    protected function createComponentProfileForm()
    {
        $form = new Form();
        $form->addPassword('old_password', 'Stare heslo')
            ->setType('password')
            ->setRequired(true);
        $form->addPassword('password', 'Nove heslo')
            ->setType('password')
            ->setRequired(true);
            $form->addSubmit('login', $this->translator->trans('tr.user.change_password'));
        $form->addHidden('return_url', $this->link('this'));
        $form->onSuccess[] = [$this, 'profileFormSucceeded'];

        return $form;
    }

    public function profileFormSucceeded(Form $form = null, $values)
    {
        $userData = $this->getUser()->getIdentity()->getData();
        $user = $this->users->repository()->findOneBy(['id'=>$userData['id']]);

        if (!\Nette\Security\Passwords::verify($values['old_password'], $user->getPassword())) {
            $this->flashMessage('tr.user.wrong_old_password', 'danger');
            return;
        } else {
            $user->setPassword(\Nette\Security\Passwords::hash($values['password']));
            $this->users->repository()->save($user);
            $this->flashMessage('tr.user.wrong_old_password');
        }

        if ($returnUrl = $form && isset($form['return_url']) ? $form['return_url']->getValue() : null) {
            $this->redirectUrl($returnUrl);
        } else {
            $this->redirect('this');
        }
    }

    //-----------------------  FORGOTTEN PASSWORD

    protected function createComponentForgottenForm()
    {
        $form = new Form();
        $form->addText('email', 'email:')
            ->setType('email')
            ->addRule(Form::EMAIL, 'Zadejte e-mail')
            ->setRequired(true);
        $form->addSubmit('login', $this->translator->trans('tr.modal.forgotten_proceed'));
        $form->onSuccess[] = [$this, 'loginForgottenSucceeded'];

        return $form;
    }

    public function loginForgottenSucceeded(Form $form = null, $values)
    {
        try{
            $user = $this->users->repository()->findOneBy(['email'=>$values['email']]);
            if (!$user) {
                $this->flashMessage('tr.user.recovery_error');
                return;
            }

            $hash = md5(md5($this->getUserKey($user)).rand());
            $this->cache->save($hash, $user->getId(), [Cache::EXPIRE => '1 hour']);

            $this->sendMail($values['email'], $this->translator->trans('tr.user.forgotten_subject'),
                [
                    'hash' => $hash,
                ],
                'forgottenMail'
            );
        } catch (\Exception $e) {
            throw $e;
        }
        $this->flashMessage('tr.user.recovery_sent');

        $this->redirect('Frontend:');
    }

    public function actionPasswordRecovery($hash)
    {
        $user = $this->users->repository()->findOneBy(['id' => $this->cache->load($hash)]);
        if (!$user) {
            $this->flashMessage('tr.user.recovery_errorě');
            $this->redirect(':Frontend:Frontend:default');
        }

        $this->cache->remove($this->getUserKey($user));
    }

    protected function createComponentRecoveryForm()
    {
        $cached = $this->cache->load($this->getParameter('hash'));
        $user = $this->users->repository()->findOneBy(['id'=>$cached]);

        $form = new RecoveryForm();
        $form->create();
        $form->setItem($user);
        $form->addSubmit('send', $this->translator->trans('tr.modal.recovery_button'))
            ->onClick[] = [$this, 'recoveryForm'];

        return $form;
    }

    public function recoveryForm(Nette\Forms\Controls\SubmitButton $button)
    {
        $data = $button->getForm()->getValues();
        $user = $this->users->repository()->findOneBy(['id'=>$data['id']]);

        try {
            $button->getForm()->process($user);

            $this->users->repository()->save($user);

            $this->flashMessage('tr.user.user_registered');
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->flashMessage('tr.user.email_unique', 'danger');
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->flashMessage('tr.user.email_unique', 'danger');
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'danger');
        }

        $credentials = (object) ['email' => $user->getEmail(), 'password' => $data['reg_password']];

        $this->loginFormSucceeded($button->getForm(), $credentials);
    }

    /**
     * @param Users $users
     */
    public function injectUsers(Users $users)
    {
        $this->users = $users;
    }

    /**
     * @param IStorage $cache
     */
    public function injectCache(IStorage $storage)
    {
        $this->cache = new Cache($storage);
    }
}
