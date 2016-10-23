<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * @ORM\Entity
 */
class Competition
{

    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_FINISHED = 'finished';

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
     * Competition slug
     *
     * @ORM\Column(type="string")
     */
    protected $slug;

    /**
     * FB APP ID
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $fb_app_id;

    /**
     * FB Secret
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $fb_secret;

    /**
     * Competition enabled
     *
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    protected $enabled = 0;

    /**
     * Votes in competition
     *
     * @ORM\OneToMany(targetEntity="App\Model\Entity\Vote", mappedBy="competition")
     */
    protected $votes;

    /**
     * When does this competition start
     *
     * @ORM\Column(type="datetime")
     */
    protected $startDate;

    /**
     * When does this competition ends
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * When does this competition ends
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $voteStartDate;

    /**
     * Till when does user may upload
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $uploadEndDate;

    /**
     * Landing image on homepage
     *
     * Unidirectional - landing image does not know about competition
     *
     * @ORM\OneToOne(targetEntity="App\Model\Entity\LandingImage")
     * @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     */
    protected $landingImage;

    /**
     * Square banner
     *
     * Unidirectional - banner does not know about competition
     *
     * @ORM\OneToOne(targetEntity="App\Model\Entity\SquareImage")
     * @ORM\JoinColumn(name="square_id", nullable=true, referencedColumnName="id")
     */
    protected $squareImage;

    /**
     * Participants of this competition
     *
     * @ORM\OneToMany(targetEntity="App\Model\Entity\Participation", mappedBy="competition")
     */
    protected $participations;

    /**
     * Participants limit
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $participants_limit;

    /**
     * Short description
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $short_description;

    /**
     * Landing page text
     *
     * @ORM\Column(type="text", nullable=true, options={"default" = ""})
     */
    protected $landing;

    /**
     * Short description
     *
     * @ORM\Column(type="text", nullable=true, options={"default" = ""})
     */
    protected $info;

    /**
     * Short description
     *
     * @ORM\Column(type="text", nullable=true, options={"default" = ""})
     */
    protected $rules;

    /**
     * FB Event url
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $event_url;

    /**
     * FB Event label
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $event_label;

    /**
     * Layout
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = "layout"})
     */
    protected $layout;


    const VOTING_TYPE_MORE_PART_IN_PERIOD = 'more_part_in_period';
    const VOTING_TYPE_ONE_PART_IN_PERIOD  = 'one_part_in_period';

    public static $voting_types = [
        self::VOTING_TYPE_MORE_PART_IN_PERIOD => 'Hlasovat pro vice fotek za interval (24 hod / 7 dni)',
        self::VOTING_TYPE_ONE_PART_IN_PERIOD  => 'Hlasovat pro 1 fotku za interval (24 hod / 7 dni)',
    ];

    /**
     * Voting type more/one part in period
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $voting_type;


    const VOTING_PERIOD_1D = 'P1D';
    const VOTING_PERIOD_7D = 'P7D';

    public static $voting_periods = [
        self::VOTING_PERIOD_1D => 'Interval 24 hodin',
        self::VOTING_PERIOD_7D => 'Interval 7 dni',
    ];

    /**
     * Voting period
     *
     * @ORM\Column(type="string", nullable=true, options={"default" = ""})
     */
    protected $voting_period;

    /**
     *
     */
    public function __construct()
    {
        $this->participations = new ArrayCollection();
        $this->votes         = new ArrayCollection();
        $this->startDate     = new \DateTime();
        $this->endDate       = new \DateTime('@' . (time() + 3600 * 30 * 24));
        $this->voteStartDate = new \DateTime();
        $this->uploadEndDate = new \DateTime('@' . (time() + 3600 * 30 * 24));
        $this->voting_type   = self::VOTING_TYPE_MORE_PART_IN_PERIOD;
        $this->voting_period = self::VOTING_PERIOD_1D;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function getFbAppId()
    {
        return $this->fb_app_id;
    }

    /**
     * @param mixed $fbAppId
     */
    public function setFbAppId($fbAppId)
    {
        $this->fb_app_id = $fbAppId;
    }

    /**
     * @return mixed
     */
    public function getFbSecret()
    {
        return $this->fb_secret;
    }

    /**
     * @param mixed $fb_secret
     */
    public function setFbSecret($fb_secret)
    {
        $this->fb_secret = $fb_secret;
    }

    /**
     * @return mixed
     */
    public function getSlugs()
    {
        return $this->slugs;
    }

    /**
     * @param mixed $slugs
     */
    public function setSlugs($slugs)
    {
        $this->slugs = $slugs;
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
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return string
     */
    public function formatStartDate($format = 'j.n.Y G:i')
    {
        return $this->startDate->format($format);
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $end
     */
    public function setEndDate($end)
    {
        $this->endDate = $end;
    }

    /**
     * @return string
     */
    public function formatEndDate($format = 'j.n.Y G:i')
    {
        return $this->endDate->format($format);
    }

    /**
     * @return mixed
     */
    public function getVoteStartDate()
    {
        return $this->voteStartDate;
    }

    /**
     * @param mixed $date
     */
    public function setVoteStartDate($date)
    {
        $this->voteStartDate = $date;
    }

    /**
     * @return string
     */
    public function formatVoteStartDate($format = 'j.n. G:i')
    {
        return $this->voteStartDate->format($format);
    }

    /**
     * @return mixed
     */
    public function getUploadEndDate()
    {
        return $this->uploadEndDate;
    }

    /**
     * @param mixed $date
     */
    public function setUploadEndDate($date)
    {
        $this->uploadEndDate = $date;
    }

    /**
     * @return string
     */
    public function formatUploadEndDate($format = 'j.n. G:i')
    {
        return $this->uploadEndDate->format($format);
    }

    /**
     * @return mixed
     */
    public function getLandingImage()
    {
        return $this->landingImage;
    }

    /**
     * @param mixed $landingImage
     */
    public function setLandingImage($landingImage)
    {
        $this->landingImage = $landingImage;
    }

    /**
     * @return mixed
     */
    public function getSquareImage()
    {
        return $this->squareImage;
    }

    /**
     * @param mixed $squareImage
     */
    public function setSquareImage($squareImage)
    {
        $this->squareImage = $squareImage;
    }

    /**
     * @return mixed
     */
    public function getParticipations()
    {
        return $this->participations;
    }

    /**
     * @return mixed
     */
//    public function getPublicParticipations()
//    {
//        return $this->participations->filter(function($x) {
//            return $x->getApproved();
//        });
//    }

    /**
     * @param mixed $participations
     */
//    public function setParticipation($participation)
//    {
//        $this->participations = $participation;
//    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $now = new \DateTime();

        if ($now < $this->getStartDate()) {
            return self::STATUS_PENDING;
        }

        if ($now > $this->getEndDate()) {
            return self::STATUS_FINISHED;
        }

        return self::STATUS_ACTIVE;
    }

    public function renderStatus()
    {
        switch ($this->getStatus()) {
            case self::STATUS_PENDING: return 'primary';
            case self::STATUS_FINISHED: return 'danger';
            default:
                return 'success';
        }
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getParticipantsLimit()
    {
        return $this->participants_limit;
    }

    /**
     * @param mixed $participantsLimit
     */
    public function setParticipantsLimit($participantsLimit)
    {
        $this->participants_limit = $participantsLimit;
    }

    /**
     * @return mixed
     */
    public function getShortDescription()
    {
        return $this->short_description;
    }

    /**
     * @param mixed $shortDescription
     */
    public function setShortDescription($shortDescription)
    {
        $this->short_description = $shortDescription;
    }

    /**
     * @return mixed
     */
    public function getLanding()
    {
        return $this->landing;
    }

    /**
     * @param mixed $landing
     */
    public function setLanding($landing)
    {
        $this->landing = $landing;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param mixed $rules
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    /**
     * @return mixed
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param mixed $layout
     */
    public function setLayout($layout)
    {
        $this->layout= $layout;
    }

    /**
     * @return mixed
     */
    public function getVotingType()
    {
        return $this->voting_type;
    }

    /**
     * @param mixed $rules
     */
    public function setVotingType($type)
    {
        if (isset(self::$voting_types[$type])) {
            $this->voting_type = $type;
        }
    }

    public function isVotingType($type)
    {
        return $type === $this->voting_type;
    }

    const INTERVAL = 'interval';
    const DATABASE = 'database';

    public function getVotingPeriod($output = self::INTERVAL)
    {
        $days = $this->voting_period == 'P1D'? 1 : 7;
        switch($output) {
            case self::DATABASE:
                return $days;
            default:
                return $this->voting_period;
        }
    }

    public function setVotingPeriod($period)
    {
        if (isset(self::$voting_periods[$period])) {
            $this->voting_period = $period;
        }
    }

}
