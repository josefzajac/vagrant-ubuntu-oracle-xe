<?php

namespace App\Model\Repository;

use App\Model\Entity\Competition;
use App\Model\Entity\CompetitionSlug;
use App\Model\Entity\Image;
use App\Model\Entity\Ip;
use App\Model\Entity\ParticipationText;
use App\Model\Entity\User;
use App\Model\Entity\Vote;

class ParticipationTexts extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getEntityClass()
    {
        return ParticipationText::class;
    }

    public function getForFront($competition)
    {
        $q = $this->repository->createQuery(
            'SELECT p, i, u FROM App\Model\Entity\ParticipationText p
             LEFT JOIN p.images i
             LEFT JOIN p.user u
             WHERE p.competition = :competition AND p.approved = 1 AND p.deleted = 0
                ORDER BY p.votes_int DESC, p.created ASC'
        )
        ->setParameter('competition', $competition);

        return $q->getResult();
    }

}
