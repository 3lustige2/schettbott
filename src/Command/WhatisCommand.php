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

            $response = $httpClient->request('GET', $this->knowledgeGraphBaseUri, [
                'query' => [
                    'key' => $apiKey,
                    'limit' => $limit,
                    'query' => $term,
                ],
            ]);

            $response = json_decode($response->getBody()->getContents());
            $items = GraphItemFactory::createItemFromArray($response->itemListElement);
            foreach ($items as $item) {
                if ($item->image !== null && $item->image->contentUrl !== null) {
                    $fileName = 'gs://schettbott.appspot.com/kg/images/'.time().'.jpg';
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
            $this->replyWithMessage(['text' => print_r($e->getMessage(), true)]);
        }
    }
}
