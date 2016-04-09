<?php

namespace DLZ\Schettbott\Entity;

use DateTime;
use GDS\Entity;

/**
 * @property string body
 * @property DateTime created_at
 * @property string status
 * @package DLZ\Schettbott\Entity
 */
class Tweet extends Entity
{
    public function __construct()
    {
        $this->setKind('Tweet');
    }
}
