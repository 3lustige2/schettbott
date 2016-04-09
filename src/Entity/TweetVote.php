<?php

namespace DLZ\Schettbott\Entity;

use GDS\Entity;

class TweetVote extends Entity
{
    public function __construct()
    {
        $this->setKind('TweetVote');
    }
}
