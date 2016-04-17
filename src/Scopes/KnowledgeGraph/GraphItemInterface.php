<?php

namespace DLZ\Schettbott\Scopes\KnowledgeGraph;

/**
 * @package DLZ\Schettbott\Scopes\KnowledgeGraph
 */
interface GraphItemInterface
{
    /**
     * @return string
     */
    public function toString();

    /**
     * @return bool
     */
    public function hasImage();
}
