<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Manager;

use Contao\DataContainer;

interface DcaAwareInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function updateDcaField(string $table, string $field, array &$config): void;

    public function saveDcaField(string $value, DataContainer $dc): string;

    /**
     * @return array<string, string>
     */
    public function getFilterOptions(DataContainer $dc): array;

    /**
     * @param array<string, mixed> $data
     */
    public function getSourceRecordsCount(array $data, DataContainer $dc): int;

    /**
     * @return array<int, int>
     */
    public function getTopTagIds(): array;
}
