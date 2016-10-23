<?php

namespace App\Model\Repository;

use App\Model\Entity\IpLog;
use Kdyby\Doctrine\EntityManager;
use Nette\Http\Request;
use Nette\Http\Response;

class IpLogs extends Repository
{
    /**
     * @var Request
     */
    private $httpRequest;

    /**
     * @var Response
     */
    private $httpResponse;

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
        return IpLog::class;
    }
}
