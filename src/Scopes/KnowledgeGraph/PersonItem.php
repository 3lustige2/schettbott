<?php

namespace DLZ\Schettbott\Scopes\KnowledgeGraph;

/**
 * @property string name
 * @property string description
 * @package DLZ\Schettbott\Scopes\KnowledgeGraph
 */
class PersonItem extends BasicItem
{
    public function toString()
    {
        return "{$this->name} is a {$this->description}";
    }
}
