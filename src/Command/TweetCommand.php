<?php

namespace DLZ\Schettbott\Command;

use Memcache;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

/**
 * @package DLZ\Schettbott\Command
 */
class TweetCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = "tweet";

    /**
     * @var string Command Description
     */
    protected $description = "Schedule a tweet";

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {
        $arguments = trim($arguments);

        $memcache = new Memcache;
        $memcache->set('tweet:'.time(), $arguments);

        $keyboard = [
            ['/tweet new'],
            ['/tweet list'],
        ];

        $reply_markup = $this->telegram->replyKeyboardMarkup(
            [
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]
        );

        $this->replyWithChatAction(['action' => Actions::TYPING]);
        $this->replyWithMessage(
            [
                'text' => 'So you want to tweet out "'.$arguments.'"',
                'reply_markup' => $reply_markup,
            ]
        );

        $this->replyWithChatAction(['action' => Actions::TYPING]);
        $this->replyWithMessage(
            [
                'text' => 'Okay. Asking the others to tweet out',
            ]
        );
    }
}
