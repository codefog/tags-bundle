<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\EventListener;

use Codefog\TagsBundle\ManagerRegistry;

class InsertTagsListener
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * TagContainer constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * On replace the insert tags.
     *
     * @param string $tag
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
     * Replace the insert tag
     *
     * @param array $elements
     *
     * @return string
     */
    private function replaceInsertTag(array $elements): string
    {
        if (count($elements) !== 3) {
            return '';
        }

        list($source, $value, $property) = $elements;

        $tag = $this->registry->get($source)->find($value);

        if (null === $tag) {
            return '';
        }

        if ('name' === $property) {
            return $tag->getName();
        }

        $data = $tag->getData();

        return isset($data[$property]) ? (string) $data[$property] : '';
    }
}
