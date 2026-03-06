<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Manager;

use Contao\DataContainer;

interface DcaAwareInterface
{
    public function updateDcaField(string $table, string $field, array &$config): void;

    public function saveDcaField(string $value, DataContainer $dc): string;

    public function getFilterOptions(DataContainer $dc): array;

    public function getSourceRecordsCount(array $data, DataContainer $dc): int;

    public function getTopTagIds(): array;
}
