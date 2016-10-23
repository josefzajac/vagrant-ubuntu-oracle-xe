<?php

namespace App\Presenters;

use App\Model\Repository\Competitions;
use App\Model\Repository\Participations;
use App\Model\Repository\ParticipationTexts;
use Nette\Application\UI\Presenter;

class BasePresenter extends Presenter
{
    const DEFAULT_LANGUAGE = 'cs';

    /** @persistent */
    public $locale;

    /** @var \App\Email\MyMail */
    protected $mail;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    /**
     * @var Competitions
     */
    protected $competitions;

    /**
     * @var Participations
     */
    protected $participations;

    /**
     * @var ParticipationTexts
     */
    protected $participationTexts;

    /**
     * Config parameters
     *
     * @var array
     */
    protected $parameters;

    /**
     */
    protected function startup()
    {
        parent::startup();
    }

    /**
     * Common render method.
     */
    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->locale = $this->locale;
    }

    protected function getImageUrl($image)
    {
        return substr($versionedUrl, 0, strpos($versionedUrl, '?'));
    }

    protected function trans($m, $params = [])
    {
        return $this->translator->trans($m, $params);
    }

    protected function sendMail($to, $subject, $params, $templateConfig, $attachments = [])
    {
        $params['_presenter'] = $this;
        $params['_controller'] = $this;
        $params['_control'] = $this;
        $params['url'] = new \Nette\Http\Url($this->getHttpRequest()->getUrl());

        if (!is_array($templateConfig)) {
            $module = explode(':', $this->getName());
            $templateConfig = [$module[0], $templateConfig];
        }
        $this->mail->send($this->parameters['admin_mail'], $to, $this->translator->trans($subject), $params, $templateConfig, $attachments);
    }

    /**
     * Saves the message to template, that can be displayed after redirect.
     * @param  string
     * @param  string
     * @return \stdClass
     */
    public function flashMessage($message, $type = 'info')
    {
        return parent::flashMessage($this->translator->trans($message), $type);
    }

    protected function uploadImage(\Nette\Http\FileUpload $upload, \App\Model\Entity\File $image, $filePrefix = null)
    {
        $name = $upload->name;

        $x = explode('/', $upload->getTemporaryFile());
        $x = explode('\\', end($x));
        $x = explode('.', end($x));
        $name = ($filePrefix ? $filePrefix.'_' : '') . reset($x) . '_' . \Nette\Utils\Strings::webalize(substr($name, 0, strrpos($name, '.')), null,true) . strtolower(substr($name, strrpos($name, '.')));

        $image->setName($name);
        $this->imagesManager->upload($upload->toImage(), $image->getNamespace(), $name);

        return $image;
    }

    /**
     * @param \App\Email\MyMail $mail
     */
    public function injectMailer(\App\Email\MyMail $mail)
    {
        $this->mail = $mail;
    }

    /**
     * @param Competitions $competitions
     */
    public function injectCompetitions(Competitions $competitions)
    {
        $this->competitions = $competitions;
    }

    /**
     * @param Participations $participations
     */
    public function injectParticipations(Participations $participations)
    {
        $this->participations = $participations;
    }

    /**
     * @param ParticipationTexts $participationTexts
     */
    public function injectParticipationTexts(ParticipationTexts $participationTexts)
    {
        $this->participationTexts = $participationTexts;
    }

    /**
     * @param \Nette\DI\Container $container
     */
    public function injectContainer(\Nette\DI\Container $container)
    {
        $this->parameters = $container->getParameters();
    }
}
