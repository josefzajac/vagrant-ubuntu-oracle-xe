<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\MagicAccessors;

/**
 * @ORM\Entity
 */
class MailHistory
{
    use MagicAccessors;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * Sent date
     *
     * @ORM\Column(type="datetime")
     */
    protected $sentDate;

    /**
     * Sent
     *
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    protected $sent;

    /**
     * From
     *
     * @ORM\Column(type="string")
     */
    protected $sender;

    /**
     * To
     *
     * @ORM\Column(type="string")
     */
    protected $receiver;

    /**
     * Subject
     *
     * @ORM\Column(type="string")
     */
    protected $subject;

    /**
     * Content
     *
     * @ORM\Column(type="text", nullable=true, options={"default" = ""})
     */
    protected $content;

    /**
     * HTML
     *
     * @ORM\Column(type="text", nullable=true, options={"default" = ""})
     */
    protected $html;

    /**
     *
     */
    public function __construct()
    {
        $this->sent     = 0;
        $this->sentDate = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function formatSentDate($format = 'j.n.Y G:i')
    {
        return $this->sentDate->format($format);
    }
}
