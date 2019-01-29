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

interface ManagerInterface
{
    /**
     * Get multiple tags optionally filtered by values
     *
     * @param array $values
     *
     * @return array
     */
    public function getMultipleTags(array $values = []): array;
}
