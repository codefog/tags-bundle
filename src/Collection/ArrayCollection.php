<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle\Collection;

class ArrayCollection implements CollectionInterface
{
    /**
     * Tags
     * @var array
     */
    protected $tags = [];

    /**
     * ArrayCollection constructor.
     *
     * @param array $tags
     */
    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->tags);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->tags);
    }
}
