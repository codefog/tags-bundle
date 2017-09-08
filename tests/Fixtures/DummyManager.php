<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Test\Fixtures;

use Codefog\TagsBundle\Collection\CollectionInterface;
use Codefog\TagsBundle\Manager\DcaAwareInterface;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\Tag;
use Contao\DataContainer;

class DummyManager implements ManagerInterface, DcaAwareInterface
{
    public function updateDcaField(array &$config): void
    {
        $config['dummy'] = true;
    }

    public function saveDcaField(string $value, DataContainer $dc): string
    {
        return strtoupper($value);
    }

    public function setAlias(string $alias): void
    {
        // TODO: Implement setAlias() method.
    }

    public function find(string $value, array $criteria = []): ?Tag
    {
        // TODO: Implement find() method.
    }

    public function findMultiple(array $criteria = []): CollectionInterface
    {
        // TODO: Implement findMultiple() method.
    }

    public function countSourceRecords(Tag $tag): int
    {
        // TODO: Implement countSourceRecords() method.
    }

    public function getSourceRecords(Tag $tag): array
    {
        // TODO: Implement getSourceRecords() method.
    }
}
