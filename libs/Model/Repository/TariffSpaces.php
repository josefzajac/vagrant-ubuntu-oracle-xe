<?php

namespace App\Model\Repository;

use App\Model\Entity\TariffSpace;

class TariffSpaces extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getEntityClass()
    {
        return TariffSpace::class;
    }
}
