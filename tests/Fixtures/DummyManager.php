<?php

namespace Codefog\TagsBundle\Test\Fixtures;

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

    public function getFilterOptions(DataContainer $dc): array
    {
        return ['foo', 'bar'];
    }

    public function setAlias(string $alias): void
    {
        // noop
    }

    public function find(string $value, array $criteria = []): ?Tag
    {
        // noop
    }

    public function findMultiple(array $criteria = []): array
    {
        // noop
    }

    public function countSourceRecords(Tag $tag): int
    {
        // noop
    }

    public function getSourceRecords(Tag $tag): array
    {
        // noop
    }
}
