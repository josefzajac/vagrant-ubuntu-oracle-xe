<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * @ORM\Entity
 * @ORM\Table(name="ACCOUNT")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ACCOUNT_TYPE", type="string")
 * @ORM\DiscriminatorMap({"CA" = "CustomerAccount", "TS" = "TariffSpace"})
 */
abstract class Account
{
    use MagicAccessors;

    /**
     * @ORM\Column(type="varchar", name="ACCOUNT_ID")
     */
    protected $account_id;

    /**
     * User constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->account_id;
    }
}
