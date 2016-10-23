<?php

namespace App\Model\Repository;

use App\Model\Entity\MailHistory;

class MailHistoryRepo extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getEntityClass()
    {
        return MailHistory::class;
    }
}
