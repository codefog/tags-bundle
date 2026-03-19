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

use Codefog\TagsBundle\Tag;

interface ManagerInterface
{
    /**
     * @return array<Tag>
     */
    public function getAllTags(string|null $source = null): array;

    /**
     * @param array<string, mixed> $values
     *
     * @return array<Tag>
     */
    public function getFilteredTags(array $values, string|null $source = null): array;
}
