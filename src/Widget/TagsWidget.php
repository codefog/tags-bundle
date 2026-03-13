<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Widget;

use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\Tag;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;

/**
 * @codeCoverageIgnore
 */
class TagsWidget extends Widget
{
    /**
     * Submit user input.
     *
     * @var bool
     */
    protected $blnSubmitInput = true;

    /**
     * Add a for attribute.
     *
     * @var bool
     */
    protected $blnForAttribute = true;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Tags manager.
     *
     * @var ManagerInterface
     */
    protected $tagsManager;

    #[\Override]
    public function addAttributes($arrAttributes = null): void
    {
        if (\is_array($arrAttributes)) {
            if ($arrAttributes['tagsManager']) {
                $this->tagsManager = System::getContainer()->get('codefog_tags.manager_registry')->get($arrAttributes['tagsManager']);
            }

            unset($arrAttributes['tagsManager']);
        }

        parent::addAttributes($arrAttributes);
    }

    #[\Override]
    public function validate(): void
    {
        $value = $this->validator($this->getPost($this->strName));

        // Validate the maximum number of items
        if (\is_array($value) && isset($this->maxItems) && \count($value) > $this->maxItems) {
            $this->addError(\sprintf($GLOBALS['TL_LANG']['ERR']['maxval'], $this->strLabel, $this->maxItems));
        }

        parent::validate();
    }

    public function generate()
    {
        if (null === $this->tagsManager) {
            return '';
        }

        $templateData = [
            'id' => $this->strId,
            'name' => $this->strName,
            'css_class' => $this->strClass,
            'hide_list' => (bool) $this->hideList,
            'all_tags' => $this->generateAllTags($this->getAllTags()),
            'js_config' => $this->generateConfig(),
        ];

        return System::getContainer()->get('twig')->render(\sprintf('@Contao/%s.html.twig', $this->customTpl ?: 'backend/widget/tags'), $templateData);
    }

    #[\Override]
    protected function getPost($strKey)
    {
        return array_filter(StringUtil::trimsplit(',', parent::getPost($strKey)));
    }

    protected function generateConfig(): array
    {
        $config = [
            'addLabel' => $GLOBALS['TL_LANG']['MSC']['cfg_tags.add'],
            'removeLabel' => $GLOBALS['TL_LANG']['MSC']['removeItem'],
            'noResultsLabel' => $GLOBALS['TL_LANG']['MSC']['noResults'],
            'allowCreate' => isset($this->tagsCreate) ? (bool) $this->tagsCreate : true,
            'sortable' => isset($this->tagsSortable) && (bool) $this->tagsSortable,
            'allTags' => $this->generateAllTags($this->getAllTags()),
            'valueTags' => $this->generateValueTags($this->getValueTags()),
        ];

        if (isset($this->maxItems)) {
            $config['maxItems'] = (int) $this->maxItems;
        }

        return $config;
    }

    protected function getAllTags(): array
    {
        return $this->tagsManager->getAllTags($this->tagsSource);
    }

    protected function getValueTags(): array
    {
        $values = \is_array($this->varValue) ? $this->varValue : [];

        if (0 === \count($values)) {
            return [];
        }

        $tags = $this->tagsManager->getFilteredTags($values, $this->tagsSource);

        // Respect the tags order
        if ($this->tagsSortable && \count($tags) > 0) {
            usort(
                $tags,
                static function (Tag $aTag, Tag $bTag) use ($values) {
                    $aIndex = array_search($aTag->getValue(), $values, true);
                    $bIndex = array_search($bTag->getValue(), $values, true);

                    return $aIndex <=> $bIndex;
                },
            );
        }

        return $tags;
    }

    private function generateValueTags(array $tags): array
    {
        $return = [];

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $return[] = $tag->getValue();
        }

        return $return;
    }

    private function generateAllTags(array $tags): array
    {
        $return = [];

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $return[] = ['value' => $tag->getValue(), 'text' => StringUtil::decodeEntities($tag->getName())];
        }

        return $return;
    }
}
