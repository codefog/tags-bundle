<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\EventListener;

use Codefog\TagsBundle\Model\TagModel;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model\Collection;

class InsertTagsListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $supportedTags = [
        'tags_title',
    ];

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * Replaces tags insert tags.
     *
     * @param string $tag
     *
     * @return string|false
     */
    public function onReplaceInsertTags($tag)
    {
        $elements = explode('::', $tag);
        $key = strtolower($elements[0]);

        if (in_array($key, $this->supportedTags, true)) {
            return $this->replaceInsertTag($key, $elements);
        }

        return false;
    }

    /**
     * Replaces a tags-related insert tag.
     *
     * @param string $insertTag
     * @param array  $elements
     *
     * @return string
     */
    private function replaceInsertTag($insertTag, array $elements)
    {
        $this->framework->initialize();

        $criteria['source'] = $elements[1];
        $idOrAlias = $elements[2];

        /** @var TagModel $adapter */
        $adapter = $this->framework->getAdapter(TagModel::class);

        if (is_numeric($idOrAlias)) {
            $criteria = [
                'values' => [$idOrAlias],
            ];
        } else {
            $criteria = [
                'aliases' => [$idOrAlias],
            ];
        }

        if (null === ($tags = $adapter->findByCriteria($criteria))) {
            return '';
        }

        return $this->generateReplacement($tags, $insertTag);
    }

    /**
     * Generates the replacement string.
     *
     * @param Collection $tags
     * @param string     $insertTag
     *
     * @return string
     */
    private function generateReplacement(Collection $tags, $insertTag)
    {
        switch ($insertTag) {
            case 'tags_title':
                return $tags->first()->name;
        }
    }
}
