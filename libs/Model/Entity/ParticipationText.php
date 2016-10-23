<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ParticipationText extends Participation
{
    /**
     * Text
     *
     * @ORM\Column(type="text", nullable=true, options={"default" = ""})
     */
    protected $text;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

}
