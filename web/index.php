<?php

ini_set('display_errors', 1);

require_once __DIR__.'/../vendor/autoload.php';

use DLZ\Schettbott\Command\StartCommand;
use DLZ\Schettbott\SchettbottApplication;
use Google\Cloud\Compute\Metadata;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Telegram\Bot\Api;

$app = new SchettbottApplication();

$app->register(
    new MonologServiceProvider(),
    [
        'monolog.logfile' => __DIR__.'/development.log',
    ]
);

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

$app['appengine.metadata'] = $app->share(
    function () {
        return new Metadata();
    }
);

$app['telegram'] = $app->share(
    function () use ($app) {
        $bot = new Api($app['appengine.metadata']->get('telegram_token'));
        $bot->addCommand(StartCommand::class);

        return $bot;
    }
);

$app->get(
    '/',
    function () {
        return new Response('Hi');
    }
);
$app->get(
    '/_ah/health',
    function (Request $request) use ($app) {
        $app->log('Health check', $request, Logger::INFO);

        return new Response('OK');
    }
);
$app->get(
    '/_ah/start',
    function (Request $request) use ($app) {
        $app->log('Instance start request', $request, Logger::INFO);

        return new Response('OK');
    }
);
$app->get(
    '/_ah/stop',
    function (Request $request) use ($app) {
        $app->log('Instance stop request', $request, Logger::INFO);

        return new Response('OK');
    }
);

$app->post(
    '/webhook/{token}',
    function (Request $request, $token) use ($app) {

        if ($token !== $app['appengine.metadata']->get('telegram_token')) {
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

$app->get(
    '/register_webhook',
    function () use ($app) {
        /** @var Api $telegram */
        $telegram = $app['telegram'];
        $token = $app['telegram.token'];

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

$app->get(
    '/env',
    function () {
        $content = var_dump($_SERVER, true);

        return new Response($content);
    }
);

$app->run();
