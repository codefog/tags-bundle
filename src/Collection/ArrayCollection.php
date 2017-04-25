<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Collection;

class ArrayCollection implements CollectionInterface
{
    /**
     * Tags.
     *
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
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->tags);
    }
}
