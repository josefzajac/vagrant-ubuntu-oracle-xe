<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;

class BasePresenter extends Presenter
{
    const DEFAULT_LANGUAGE = 'cs';


    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    /**
     * Config parameters
     *
     * @var array
     */
    protected $parameters;

    /**
     * Config Menu
     *
     * @var array
     */
    protected $menuEntities = [];

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

    protected function trans($m, $params = [])
    {
        return $this->translator->trans($m, $params);
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

    /**
     * @param \Nette\DI\Container $container
     */
    public function injectContainer(\Nette\DI\Container $container)
    {
        $this->parameters = $container->getParameters();
        $this->menuEntities = $this->parameters['menu_entities'];
    }
}
