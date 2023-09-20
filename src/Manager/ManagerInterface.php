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

interface ManagerInterface
{
    /**
     * Get all tags.
     */
    public function getAllTags(string $source = null): array;

    /**
     * Get tags optionally filtered by values.
     */
    public function getFilteredTags(array $values, string $source = null): array;

    /**
     * Get locale.
     */
    public function getLocale(): string;

    /**
     * Get validChars.
     */
    public function getValidChars(): string;
}
