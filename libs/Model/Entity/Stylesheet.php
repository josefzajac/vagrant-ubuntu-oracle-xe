<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Stylesheet extends File
{
    protected $namespace = '/assets/stylesheets';
}
