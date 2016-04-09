<?php

namespace DLZ\Schettbott\Service;

use DLZ\Schettbott\Entity\Tweet;
use DLZ\Schettbott\Entity\TweetVote;
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
     * @return Tweet[]
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

    /**
     * @param Tweet[] $tweets
     * @return TweetVote[]
     */
    public function findVotesByTweets(array $tweets = [])
    {
        $votes = [];

        foreach ($tweets as $tweet) {
            $votes[$tweet->getKeyId()] = $this->tweetVoteStore
                ->fetchAll(
                    "SELECT * FROM TweetVote WHERE __key__ HAS ANCESTOR @tweet",
                    [
                        'tweet' => $tweet,
                    ]
                );
        }

        return $votes;
    }
}
