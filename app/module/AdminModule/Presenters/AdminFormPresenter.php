<?php

namespace App\AdminModule\Presenters;

use App\Model\Repository\Authors;
use App\Model\Factory\EntityFactory;
use Nette;

abstract class AdminFormPresenter extends AdminPresenter
{
    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var Authors
     */
    protected $authors;

    /**
     * @var Repository
     */
    protected $repo;

    protected function startup()
    {
        parent::startup();

        $this->template->total = 0;
        $this->template->limit = 10;
    }

    public function actionDefault($page = 1, $limit = 10)
    {
        $this->template->total = $total = 100; //$this->repo->countBy();
        $this->template->limit = $limit;
        $this->template->all_items = $this->db->query('select * from TARIFF_SPACE %lmt', 10)->fetchAll();
    }

    public function renderDetail($id)
    {
        if ($id) {
            $this->template->item = $this->repo->repository()->findOneById($id);
        } else {
            $this->template->item = $this->entityFactory->create($this->getEntityName());
        }

        if (is_null($this->template->item)) {
            throw new \Nette\Application\BadRequestException($this->getEntityName() . '_not_found');
        }

        $this['editItemForm']->setItem($this->template->item);

        $this->template->class = $this->getEntityName();
    }

    public function createComponentEditItemForm()
    {
        $formLabel = $this->getFormName();
        $form      = new $formLabel($this, 'editItemForm');
        $form->create();
        $form->addSubmit('send', 'Uložit')
            ->onClick[] = [$this, 'doItemForm'];

        return $form;
    }

    public function doItemForm(Nette\Forms\Controls\SubmitButton $button, $redirectDestination = 'default', $redirectParameters = [])
    {
        $data    = $button->getForm()->getValues();
        $id      = $data['id'];
        $item    = $this->repo->repository()->findOneById($id);
        $newItem = false;
        if (is_null($item)) {
            $item    = $this->entityFactory->create($this->getEntityName());
            $newItem = true;
        }
        try {
            $button->getForm()->process($item);
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'error');

            return;
        }
        try {
            $this->preSave($item, $data, $this->repo->entityManager());
            $this->repo->repository()->save($item);
            if ($newItem) {
                $this->flashMessage('tr.admin.item_added');
            } else {
                $this->flashMessage('tr.admin.item_edited');
            }
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'error');
        }

        $this->redirect($redirectDestination, $redirectParameters);
    }

    protected function preSave($item, $data, $em)
    {
    }
    abstract protected function getEntityName();
    abstract protected function getFormName();
}
