<?php

namespace App\Model\Repository;

use App\Model\Entity\Competition;
use App\Model\Entity\Participation;
use App\Model\Entity\Ip;
use App\Model\Entity\Vote;

class Votes extends Repository
{
    /**
     * @inheritdoc
     */
    protected function getEntityClass()
    {
        return Vote::class;
    }

    /**
     * @param  Participation $participation
     * @param  Ip          $ip
     * @return null|Vote
     */
    public function findLast(Participation $participation = null, Ip $ip, Competition $competition = null)
    {
        if (null === $participation)
            return $this->repository->findOneBy(
                [
                    'ip'          => $ip,
                    'competition' => $competition,
                ],
                ['timestamp' => 'DESC']
            );

        return $this->repository->findOneBy(
            [
                'ip'            => $ip,
                'participation' => $participation,
            ],
            ['timestamp' => 'DESC']
        );
    }

    public function getForFront(Competition $competition, $ip)
    {
        $q = $this->repository->createQuery(
            'SELECT v FROM App\Model\Entity\Vote v
             LEFT JOIN v.ip ip
             LEFT JOIN v.participation p
             LEFT JOIN p.competition c
             WHERE p.competition = :competition
                 AND ip.address = :address
                 AND DATE_DIFF(v.timestamp, CURRENT_TIMESTAMP()) >= :period'
        )
            ->setParameter('competition', $competition)
            ->setParameter('period', -1*$competition->getVotingPeriod(Competition::DATABASE))
            ->setParameter('address', $ip);

        return $q->getResult();
    }
}
