<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Manager;

interface InsertTagsAwareInterface
{
    /**
     * @param array<string> $elements
     */
    public function getInsertTagValue(string $value, string $property, array $elements): string;
}
