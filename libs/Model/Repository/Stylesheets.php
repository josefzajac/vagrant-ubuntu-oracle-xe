<?php

namespace App\Model\Repository;

use App\Model\Entity\Stylesheet;

class Stylesheets extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getEntityClass()
    {
        return Stylesheet::class;
    }
}
