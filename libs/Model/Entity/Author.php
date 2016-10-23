<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Author extends User
{
    /**
     * @ORM\Column(type="string")
     */
    protected $nick;

    /**
     * @ORM\Column(type="string")
     */
    protected $age;

    /**
     * @ORM\Column(type="string")
     */
    protected $phone;

    /**
     * @ORM\Column(type="string")
     */
    protected $parentEmail;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $schoolName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $schoolStreet;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $schoolCity;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * @param mixed $nick
     */
    public function setNick($nick)
    {
        $this->nick = $nick;
    }

    /**
     * @return mixed
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param mixed $age
     */
    public function setAge($val)
    {
        $this->age = $val;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($val)
    {
        $this->phone = $val;
    }

    /**
     * @return mixed
     */
    public function getParentEmail()
    {
        return $this->parentEmail;
    }

    /**
     * @param mixed $parentEmail
     */
    public function setParentEmail($val)
    {
        $this->parentEmail = $val;
    }

    /**
     * @return mixed
     */
    public function getSchoolName()
    {
        return $this->schoolName;
    }

    /**
     * @param mixed $schoolName
     */
    public function setSchoolName($val)
    {
        $this->schoolName = $val;
    }

    /**
     * @return mixed
     */
    public function getSchoolStreet()
    {
        return $this->schoolStreet;
    }

    /**
     * @param mixed $schoolStreet
     */
    public function setSchoolStreet($val)
    {
        $this->schoolStreet = $val;
    }

    /**
     * @return mixed
     */
    public function getSchoolCity()
    {
        return $this->schoolCity;
    }

    /**
     * @param mixed $schoolCity
     */
    public function setSchoolCity($val)
    {
        $this->schoolCity = $val;
    }

}
