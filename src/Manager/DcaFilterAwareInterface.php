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

// @todo – merge this interface with DcaAwareInterface in next major version

interface DcaFilterAwareInterface
{
    /**
     * Get the filter options.
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getFilterOptions(DataContainer $dc): array;
}
