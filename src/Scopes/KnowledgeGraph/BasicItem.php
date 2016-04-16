<?php

namespace DLZ\Schettbott\Scopes\KnowledgeGraph;

/**
 * @property string description
 * @property string name
 * @package DLZ\Schettbott\Scopes\KnowledgeGraph
 */
class BasicItem implements GraphItemInterface
{
    /**
     * @var object
     */
    protected $object;

    /**
     * Person constructor.
     * @param object $object Decoded API result
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    public function __get($property)
    {
        if ($this->object->{$property}) {
            return $this->object->{$property};
        }

        return null;
    }

    public function toString()
    {
        return "{$this->name} is a {$this->description}";
    }
}
