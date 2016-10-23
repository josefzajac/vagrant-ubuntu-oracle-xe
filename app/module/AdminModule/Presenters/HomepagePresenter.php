<?php

namespace App\AdminModule\Presenters;

use App\Model\Repository\MailHistoryRepo;

class HomepagePresenter extends AdminPresenter
{

    /**
     * @var MailHistoryRepo
     */
    protected $historyRepo;

    public function actionDefault()
    {
        $this->redirect(':Admin:TariffSpace:default');
    }

    public function actionMailHistory($page = 1, $limit = 5)
    {
        $this->template->total = $total = $this->historyRepo->countBy();
        $this->template->limit = $limit;
        $this->template->items = $this->historyRepo->repository()->findBy([], ['sentDate'=>'desc'], $limit, ($page-1) * $limit);
    }

    public function actionErrors()
    {
        $this->template->error_log = file_get_contents($this->parameters['appDir'] . '/../log/error.log');
        $this->template->exception_log = file_get_contents($this->parameters['appDir'] . '/../log/exception.log');
    }

    /**
     * @param MailHistoryRepo $competitions
     */
    public function injectMailHistory(MailHistoryRepo $historyRepo)
    {
        $this->historyRepo = $historyRepo;
    }
}
