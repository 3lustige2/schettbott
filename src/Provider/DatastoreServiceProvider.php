<?php

namespace DLZ\Schettbott\Provider;

use DLZ\Schettbott\Entity\Tweet;
use DLZ\Schettbott\Entity\TweetVote;
use DLZ\Schettbott\Service\TweetService;
use GDS\Schema;
use GDS\Store;
use Silex\Application;
use Silex\ServiceProviderInterface;

class DatastoreServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['store.tweet'] = $app->share(
            function () {
                $tweetSchema = (new Schema('Tweet'))
                    ->addString('body')
                    ->addDatetime('created_at')
                    ->addString('status');

                $store = new Store($tweetSchema);
                $store->setEntityClass(Tweet::class);

                return $store;
            }
        );

        $app['store.tweet_vote'] = $app->share(
            function () {
                $tweetSchema = (new Schema('TweetVote'))
                    ->addString('user_id')
                    ->addDatetime('created_at');

                $store = new Store($tweetSchema);
                $store->setEntityClass(TweetVote::class);

                return $store;
            }
        );

        $app['tweet_service'] = $app->share(
            function () use ($app) {

                return new TweetService($app['store.tweet'], $app['store.tweet_vote']);
            }
        );
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}
