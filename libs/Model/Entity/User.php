<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"user" = "User", "author" = "Author"})
 */
class User
{
    const REGISTERED = 'registered';
    const AUTHOR     = 'author';
    const JURY       = 'jury';
    const ADMIN      = 'administrator';

    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fid;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fb_token;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Model\Entity\Participation", mappedBy="user")
     */
    protected $participations;

    /**
     * When was voted
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->created        = new \Datetime();
        $this->participations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFid()
    {
        return $this->fid;
    }

    /**
     * @param mixed $fid
     */
    public function setFid($fid)
    {
        $this->fid = $fid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFbToken()
    {
        return $this->fb_token;
    }

    /**
     * @param mixed $token
     */
    public function setFbToken($token)
    {
        $this->fb_token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function export()
    {
        return [
            'id'    => $this->id,
            'fid'   => $this->fid,
            'name'  => $this->name,
            'email' => $this->email,
            'roles' => $this->roles,
        ];
    }
}
