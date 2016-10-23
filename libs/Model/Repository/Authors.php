<?php

namespace App\Model\Repository;

use App\Model\Entity\Author;

class Authors extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getEntityClass()
    {
        return Author::class;
    }
}
