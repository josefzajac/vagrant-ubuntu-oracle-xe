<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="CUSTOMER_ACCOUNT")
 *
 */
class CustomerAccount extends Account
{
    use MagicAccessors;

    /**
     *
     * @ORM\Column(type="integer")
     */
    protected $operator;

    /**
     *
     * @ORM\Column(type="boolean")
     */
    protected $customerId;

    /**
     * Vote constructor.
     */
    public function __construct()
    {
    }
}
