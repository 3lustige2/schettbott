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
    /**
     * Transient property
     *
     * @var TweetVote[]
     */
    protected $votes;

    public function __construct()
    {
        $this->setKind('Tweet');

        $this->votes = [];
    }

    /**
     * @return TweetVote[]
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param TweetVote[] $votes
     * @return Tweet
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;

        return $this;
    }
}
