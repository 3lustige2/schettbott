<?php

ini_set('display_errors', 1);

require_once __DIR__.'/../vendor/autoload.php';

use DLZ\Schettbott\Command\StartCommand;
use DLZ\Schettbott\Command\TweetCommand;
use DLZ\Schettbott\SchettbottApplication;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Telegram\Bot\Api;

$dotenv = new Dotenv\Dotenv(__DIR__.'/../', 'env');
$dotenv->load();

$app = new SchettbottApplication();

/**
 * Add logging so it can be easily monitored on
 * the cloud console.
 */
$app->register(
    new MonologServiceProvider(),
    [
        'monolog.logfile' => __DIR__.'/development.log',
    ]
);

/**
 * Override all current loghandlers in favor of
 * a syslog handler.
 */
$app['monolog'] = $app->share(
    $app->extend(
        'monolog',
        function ($monolog, $app) {
            /** @var Monolog\Logger $monolog */
            $monolog->setHandlers(
                [
                    new SyslogHandler('coollog'),
                ]
            );

            return $monolog;
        }
    )
);

/**
 * The telegram API instance.
 */
$app['telegram'] = $app->share(
    function () use ($app) {
        $bot = new Api(getenv('TELEGRAM_TOKEN'));
        $bot->addCommands(
            [
                StartCommand::class,
                TweetCommand::class,
            ]
        );

        return $bot;
    }
);

/**
 * Dummy index route
 */
$app->get(
    '/',
    function () {
        return new Response('Hi');
    }
);

$app->post(
    '/webhook/{token}',
    function (Request $request, $token) use ($app) {

        if ($token !== getenv('TELEGRAM_TOKEN')) {
            $app->log('Telegram token doesn\'t match', ['request' => $request], Logger::ERROR);

            return new Response('Wrong token.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        /** @var Api $telegram */
        $telegram = $app['telegram'];

        $app->log('Received webhook', ['request' => $request], Logger::INFO);
        $update = $telegram->commandsHandler(true);

        return new Response('Work it.');
    }
);

/**
 * Registers a webhook to the Telegram platform with the current module
 */
$app->get(
    '/register_webhook',
    function () use ($app) {
        /** @var Api $telegram */
        $telegram = $app['telegram'];
        $token = getenv('TELEGRAM_TOKEN');

        $result = $telegram->setWebhook(
            [
                'url' => "https://schettbott.appspot.com/webhook/{$token}",
            ]
        );

        if ($result->isError()) {
            return new Response('Could not register webhook', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('Webhook registered');
    }
);

/**
 * De-registers the webhook URL for the Bot with the current API
 * key.
 */
$app->get(
    '/unregister_webhook',
    function (Request $request) use ($app) {
        /** @var Api $telegram */
        $telegram = $app['telegram'];

        $responce = $telegram->removeWebhook();
        if ($responce->isError()) {
            $app->log('Error unregistering webhook', ['request' => $request], Logger::ERROR);

            return new Response('Error removing webhook', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('Webhook removed');
    }
);

/**
 * Displays information about the current environment
 */
$app->get(
    '/env',
    function () {
        $serverVars = $_SERVER;
        unset($serverVars['TELEGRAM_TOKEN']);
        $content = var_dump($serverVars, true);

        return new Response('<pre>'.$content.'</pre>');
    }
);

$app->run();
