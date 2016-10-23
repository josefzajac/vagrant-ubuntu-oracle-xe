<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="TARIFF_SPACE")
 */
class TariffSpace extends Account
{
    /**
     * @ORM\Column(name="HISTNO")
     */
    protected $histno;

    /**
     * @ORM\Column(name="STATUS")
     */
    protected $status;

    /**
     * @ORM\Column(name="TECHNICAL_STATUS")
     */
    protected $technicalStatus;

    public function __construct()
    {
        parent::__construct();
    }

}
