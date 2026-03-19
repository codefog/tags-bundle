<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Test\Fixtures;

use Codefog\TagsBundle\Manager\DcaAwareInterface;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Contao\DataContainer;

class DummyManager implements ManagerInterface, DcaAwareInterface
{
    public function updateDcaField(string $table, string $field, array &$config): void
    {
        $config['dummy'] = true;
    }

    public function saveDcaField(string $value, DataContainer $dc): string
    {
        return 'FOOBAR';
    }

    public function getFilterOptions(DataContainer $dc): array
    {
        return ['foo', 'bar'];
    }

    public function getSourceRecordsCount(array $data, DataContainer $dc): int
    {
        return 0;
    }

    public function getAllTags(string|null $source = null): array
    {
        return [];
    }

    public function getFilteredTags(array $values, string|null $source = null): array
    {
        return [];
    }

    public function getTopTagIds(): array
    {
        return [1 => 1, 2 => 4, 3 => 9];
    }
}
