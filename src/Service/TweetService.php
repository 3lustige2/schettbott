<?php

namespace DLZ\Schettbott\Service;

use GDS\Store;

class TweetService
{
    /**
     * @var Store
     */
    protected $tweetStore;

    /**
     * @var Store
     */
    protected $tweetVoteStore;

    /**
     * @param Store $tweetStore
     * @param Store $tweetVoteStore
     */
    public function __construct(Store $tweetStore, Store $tweetVoteStore)
    {
        $this->tweetStore = $tweetStore;
        $this->tweetVoteStore = $tweetVoteStore;
    }

    /**
     * @param string $status
     * @return \DLZ\Schettbott\Entity\Tweet[]
     */
    public function findTweetsByStatus($status)
    {
        return $this->tweetStore
            ->fetchAll(
                "SELECT * FROM Tweet WHERE status = @status ORDER BY created_at ASC",
                [
                    'status' => $status,
                ]
            );
    }
}
