<?php

namespace App\Model\Repository;

use App\Model\Entity\Competition;
use App\Model\Entity\CompetitionSlug;

class Competitions extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getEntityClass()
    {
        return Competition::class;
    }

    /**
     * @param $slug
     * @return Competition
     */
    public function findOneBySlug($slug)
    {
        return $this->repository->findOneBy(['slug'=>$slug]);
    }
}
