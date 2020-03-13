<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Manager;

use Contao\DataContainer;

interface DcaAwareInterface
{
    /**
     * Update the DCA field.
     */
    public function updateDcaField(array &$config): void;

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
}
