<?php

namespace Codefog\TagsBundle\Test\Fixtures\Model;

class Collection implements \IteratorAggregate
{
    private $models = [];

    /**
     * @param array $models
     */
    public function __construct(array $models)
    {
        $this->models = $models;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->models);
    }
}
