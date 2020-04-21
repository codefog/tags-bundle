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

interface InsertTagsAwareInterface
{
    /**
     * Get the insert tag value.
     */
    public function getInsertTagValue(string $value, string $property, array $elements): string;
}
