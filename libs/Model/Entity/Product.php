<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * @ORM\Entity
 * @ORM\Table(name="PRODUCT")
 */
class PRODUCT
{
    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
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
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $hashTime;

    /**
     * vote hash
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $hash;

    /**
     * All votes from this IP adress
     *
     * @ORM\OneToMany(targetEntity="App\Model\Entity\Vote", mappedBy="ip")
     */
    protected $votes;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param mixed $votes
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;
    }
}
