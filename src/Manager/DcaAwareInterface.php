<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle\Manager;

use Contao\DataContainer;

interface DcaAwareInterface
{
    /**
     * Update the DCA field
     *
     * @param array $config
     */
    public function updateDcaField(array &$config): void;

    /**
     * Save the DCA field
     *
     * @param string        $value
     * @param DataContainer $dc
     */
    public function saveDcaField(string $value, DataContainer $dc): string;
}
