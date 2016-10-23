<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * @ORM\Entity
 */
class IpLog
{
    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * IP address
     *
     * @ORM\Column(type="string")
     */
    protected $address;

    /**
     * vote hash
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $part;

    /**
     * vote hash
     *
     * @ORM\Column(type="datetime")
     */
    protected $inserted;

    /**
     * vote hash
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $hash;

    /**
     * vote hash
     *
     * @ORM\Column(type="text", nullable=true, options={"default" = ""})
     */
    protected $cookie;

    /**
     * vote hash
     *
     * @ORM\Column(type="text", nullable=true, options={"default" = ""})
     */
    protected $headers;

    /**
     * @return mixed
     */
    public function __construct()
    {
        $this->inserted     = new \DateTime();
    }

}
