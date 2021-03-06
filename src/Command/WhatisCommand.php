<?php
namespace DLZ\Schettbott\Command;

use DLZ\Schettbott\Scopes\KnowledgeGraph\GraphItemFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class WhatisCommand extends Command
{
    /**
     * @var string
     */
    private $knowledgeGraphBaseUri = 'https://kgsearch.googleapis.com/v1/entities:search';

    /**
     * @var string Command Name
     */
    protected $name = "whatis";

    /**
     * @var string Command Description
     */
    protected $description = "Use knowledge graph to find something";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $term = $arguments;
        $apiKey = getenv('GOOGLE_APIKEY');
        // This will update the chat status to typing...
        $this->replyWithChatAction(['action' => Actions::TYPING]);
        $limit = 1;

        $httpClient = new Client();
        try {
            $parts = explode(';', $arguments);
            $partCount = count($parts);
            if ($partCount > 1) {
                $limit = (int)$parts[$partCount - 1];
                $term = $parts[0];
            }

            if ((int)$limit > 10) {
                $this->replyWithPhoto([
                    'photo' => 'http://www.familysecuritymatters.org/imgLib/20140717_ObamaMiddleFingerL.jpg',
                    'caption' => 'Hartes Limit bei 10 items. Reset.',
                ]);

                $limit = 10;
            }

            $response = $httpClient->request('GET', $this->knowledgeGraphBaseUri, [
                'query' => [
                    'key' => $apiKey,
                    'limit' => $limit,
                    'query' => $term,
                ],
            ]);

            $response = json_decode($response->getBody()->getContents());
            $items = GraphItemFactory::createItemFromArray($response->itemListElement);

            if (count($items) === 0) {
                $this->replyWithMessage([
                       'text' => 'Keine Ergebnisse. <a href="https://www.google.com/#q=' . $term. '">Suche nach ' . $term . '</a>.',
                       'parse_mode' => 'HTML',
                       'disable_web_page_preview' => true,
                   ]);
            }

            foreach ($items as $item) {
                if ($item->hasImage()) {

                    $nameHash = sha1($item->image->contentUrl);

                    $fileName = 'gs://schettbott.appspot.com/kg/images/'.$nameHash.'.jpg';
                    $result = file_get_contents($item->image->contentUrl);
                    file_put_contents($fileName, $result);

                    $this->replyWithPhoto([
                        'photo' => $fileName,
                        'caption' => "{$item->toString()} \n{$item->description}. License: {$item->image->license}",
                    ]);
                } else {
                    $this->replyWithMessage(['text' => $item->toString()]);
                }
            }
        } catch (ClientException $e) {
            $this->replyWithMessage([
                'text' => '<a href="https://github.com/3lustige2/schettbott/wiki/1460833560">Uups</a>.',
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);
        }
    }
}
