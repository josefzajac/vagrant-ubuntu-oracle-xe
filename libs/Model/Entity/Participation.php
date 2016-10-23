<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"participation" = "Participation", "participation_text" = "ParticipationText"})
 */
class Participation
{
    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * Name of the competition
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * To what user does this participation in competition belongs
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\User", inversedBy="participations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * To which competitions user does this participation belongs
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Entity\Competition", inversedBy="participations")
     * @ORM\JoinColumn(name="competition_id", referencedColumnName="id")
     */
    protected $competition;

    /**
     * Assigned votes from users
     *
     * @ORM\OneToMany(targetEntity="App\Model\Entity\Vote", mappedBy="participation")
     */
    protected $votes;

    /**
     * Integer representation of votes
     *
     * @ORM\Column(type="integer")
     */
    protected $votes_int;

    /**
     * When was created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * When was approved
     *
     * @ORM\Column(type="boolean", options={"default" = 1})
     */
    protected $approved;

    /**
     * New participant, admin didn't saw it yet
     *
     * @ORM\Column(type="boolean", options={"default" = 1})
     */
    protected $new;

    /**
     * When was deleted
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $deleted;

    /**
     * When was declined
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deleted_when;

    /**
     * Who deleted
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $deleted_who;

    public $voted;

    public function __construct()
    {
        $this->votes_int = 0;
        $this->approved  = 0;
        $this->new       = 1;
        $this->deleted   = 0;
        $this->created   = new \DateTime();
        $this->images    = new ArrayCollection();
        $this->votes     = new ArrayCollection();
        $this->deleted_who = 0;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param mixed $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getCompetition()
    {
        return $this->competition;
    }

    /**
     * @param mixed $competition
     */
    public function setCompetition($competition)
    {
        $this->competition = $competition;
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

    /**
     * @return mixed
     */
    public function getVotesInt()
    {
        return $this->votes_int;
    }

    /**
     * @param $votes
     */
    public function increaseVotes($votes)
    {
        $this->votes_int = $this->votes_int + $votes;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    public function getVoteSummary()
    {
        $ips = [];
        foreach ($this->getVotes() as $v) {
            if ($v->getIp()) {
                $a = $v->getIp()->getAddress();
                if (false !==strpos($a, ':'))
                    continue;
                if (!isset($ips[$a]))
                    $ips[$a] = 0;

                $ips[$a]++;
            }
        }

        return $ips;
    }

    public function getVoteAverage()
    {
        $ips = $this->getVoteSummary();
        if (count($ips))
            return round(array_sum($ips) / count($ips), 2);

        return 0;
    }
}
