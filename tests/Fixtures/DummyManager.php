<?php

namespace Codefog\TagsBundle\Test\Fixtures;

use Codefog\TagsBundle\Manager\DcaAwareInterface;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\Tag;
use Contao\DataContainer;

class DummyManager implements ManagerInterface, DcaAwareInterface
{
    /**
     * @inheritDoc
     */
    public function updateDcaField(string $table, string $field, array &$config): void
    {
        $config['dummy'] = true;
    }

    /**
     * @inheritDoc
     */
    public function saveDcaField(string $value, DataContainer $dc): string
    {
        return 'FOOBAR';
    }

    /**
     * @inheritDoc
     */
    public function getFilterOptions(DataContainer $dc): array
    {
        return ['foo', 'bar'];
    }

    /**
     * @inheritDoc
     */
    public function getSourceRecordsCount(array $data, DataContainer $dc): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getAllTags(string $source = null): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getFilteredTags(array $values, string $source = null): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTopTagIds(): array
    {
        return [1 => 1, 2 => 4, 3 => 9];
    }
}
