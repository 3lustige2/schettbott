<?php

namespace DLZ\Schettbott\Scopes\KnowledgeGraph;

/**
 * @package DLZ\Schettbott\Scopes\KnowledgeGraph
 */
class GraphItemFactory
{
    /**
     * @param array $rawItemsArray
     * @return GraphItemInterface[]
     */
    public static function createItemFromArray($rawItemsArray = [])
    {
        $convertedItems = [];
        foreach ($rawItemsArray as $rawItem) {
            $convertedItems[] = static::createItem($rawItem);
        }

        return $convertedItems;
    }

    /**
     * @param object $rawItem
     * @return GraphItemInterface
     */
    public static function createItem($rawItem)
    {
        switch ($rawItem->result->{'@type'}[0]) {
            case 'Person':
                return new PersonItem($rawItem->result);
                break;
            default:
                return new BasicItem($rawItem->result);
        }
    }
}
