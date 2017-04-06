<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle\Manager;

use Codefog\TagsBundle\Collection\CollectionInterface;
use Codefog\TagsBundle\Tag;

interface ManagerInterface
{
    /**
     * Set the alias
     *
     * @param string $alias
     */
    public function setAlias(string $alias): void;

    /**
     * Find the tag by value
     *
     * @param string $value
     * @param array  $criteria
     *
     * @return Tag|null
     */
    public function find(string $value, array $criteria = []): ?Tag;

    /**
     * Find the multiple tags
     *
     * @param array $criteria
     *
     * @return CollectionInterface
     */
    public function findMultiple(array $criteria = []): CollectionInterface;

    /**
     * Count the source records
     *
     * @param Tag $tag
     *
     * @return int
     */
    public function countSourceRecords(Tag $tag): int;

    /**
     * Get the source records
     *
     * @param Tag $tag
     *
     * @return array
     */
    public function getSourceRecords(Tag $tag): array;
}
