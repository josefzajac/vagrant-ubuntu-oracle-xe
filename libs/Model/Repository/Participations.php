<?php

namespace App\Model\Repository;

use App\Model\Entity\Competition;
use App\Model\Entity\CompetitionSlug;
use App\Model\Entity\Image;
use App\Model\Entity\Ip;
use App\Model\Entity\Participation;
use App\Model\Entity\User;
use App\Model\Entity\Vote;

class Participations extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getEntityClass()
    {
        return Participation::class;
    }

    public function getForFront($competition)
    {
        $q = $this->repository->createQuery(
            'SELECT p, i, u FROM App\Model\Entity\Participation p
             LEFT JOIN p.images i
             LEFT JOIN p.user u
             WHERE p.competition = :competition AND p.approved = 1 AND p.deleted = 0
                ORDER BY p.votes_int DESC, p.created ASC'
        )
        ->setParameter('competition', $competition);

        return $q->getResult();
    }

    public function getUserParticipantApproved($competition, $user)
    {
        $qb = $this->repository->createQueryBuilder()
            ->select('part')
            ->from(Participation::class, 'part')
            ->join(Image::class, 'image')
            ->andWhere('part.competition = :competition')
            ->andWhere('part.user= :user')
            ->andWhere('part.approved = 1')
            ->andWhere('part.deleted = 0')
            ->orderBy('part.votes_int', 'DESC')
            ->setParameter('competition', $competition)
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

}
