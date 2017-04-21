<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Manager;

use Contao\DataContainer;

interface DcaAwareInterface
{
    /**
     * Update the DCA field.
     *
     * @param array $config
     */
    public function updateDcaField(array &$config): void;

    /**
     * Save the DCA field.
     *
     * @param string        $value
     * @param DataContainer $dc
     */
    public function saveDcaField(string $value, DataContainer $dc): string;
}
