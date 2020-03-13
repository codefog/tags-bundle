<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\EventListener;

use Codefog\TagsBundle\Manager\InsertTagsAwareInterface;
use Codefog\TagsBundle\ManagerRegistry;

class InsertTagsListener
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * TagContainer constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * On replace the insert tags.
     *
     * @return string|bool
     */
    public function onReplaceInsertTags(string $tag)
    {
        $elements = explode('::', $tag);
        $key = strtolower(array_shift($elements));

        if ('tag' === $key) {
            return $this->replaceInsertTag($elements);
        }

        return false;
    }

    /**
     * Replace the insert tag.
     */
    private function replaceInsertTag(array $elements): string
    {
        if (3 !== \count($elements)) {
            return '';
        }

        [$source, $value, $property] = $elements;

        $manager = $this->registry->get($source);

        if ($manager instanceof InsertTagsAwareInterface) {
            return $manager->getInsertTagValue($value, $property, $elements);
        }

        return '';
    }
}
