<?php

namespace App\Model\Repository;

use App\Model\Entity\Ip;
use Kdyby\Doctrine\EntityManager;
use Nette\Http\Request;
use Nette\Http\Response;

class Ips extends Repository
{
    const IP_CACHE_PREFIX = 'IP_';

    /**
     * @var Request
     */
    private $httpRequest;

    /**
     * @var Response
     */
    private $httpResponse;

    private $ipExclude = [
        '82.202.118.237',
    ];

    private $hackCookieKey = 'hack_ip';

    /**
     * CompetitionRepository constructor.
     * @param EntityManager $em
     * @param Request       $httpRequest
     */
    public function __construct(EntityManager $em, Request $httpRequest, Response $httpResponse)
    {
        parent::__construct($em);

        $this->httpRequest  = $httpRequest;
        $this->httpResponse = $httpResponse;
    }

    /**
     * @inheritdoc
     */
    protected function getEntityClass()
    {
        return Ip::class;
    }

    /**
     * Fetch current IP address from request object
     *
     * @return NULL|string
     */
    public function getRemoteIp()
    {
        $ip = $this->httpRequest->getCookie('ip');
        if (in_array($ip, $this->ipExclude)) {
            if (!($hack = $this->httpRequest->getCookie($this->hackCookieKey))) {
                $hack = rand();
                $this->httpResponse->setCookie($this->hackCookieKey, $hack, '24 hours');
            }
            $ip .= ':' . $hack;
        }

        return $ip;
    }

    /**
     * Return IP instance for current address
     *
     * @return null|Ip
     */
    public function getCurrent()
    {
        return $this->repository->findOneBy(['address' => $this->getRemoteIp()]);
    }

    /**
     * Creates new DB record for current IP
     *
     * @throws \Exception
     * @return Ip
     */
    public function create()
    {
        // Create instance
        $ip = new Ip();
        $ip->setAddress($this->getRemoteIp());

        // Persist
        $this->em->persist($ip);
        $this->em->flush();

        return $ip;
    }
}
