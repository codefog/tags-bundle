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

interface InsertTagsAwareInterface
{
    /**
     * Get the insert tag value
     *
     * @param string $value
     * @param string $property
     * @param array $elements
     *
     * @return string
     */
    public function getInsertTagValue(string $value, string $property, array $elements): string;
}
