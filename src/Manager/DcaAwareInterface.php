<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Manager;

use Contao\DataContainer;

interface DcaAwareInterface
{
    /**
     * Update the DCA field.
     */
    public function updateDcaField(string $table, string $field, array &$config): void;

    /**
     * Save the DCA field.
     */
    public function saveDcaField(string $value, DataContainer $dc): string;

    /**
     * Get the filter options.
     */
    public function getFilterOptions(DataContainer $dc): array;

    /**
     * Get the source records count.
     */
    public function getSourceRecordsCount(array $data, DataContainer $dc): int;

    /**
     * Get the top tag IDs with count.
     */
    public function getTopTagIds(): array;
}
