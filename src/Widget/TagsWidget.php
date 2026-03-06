<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Widget;

use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\Tag;
use Contao\BackendTemplate;
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
    public function addAttributes($attributes = null): void
    {
        if (\is_array($attributes)) {
            if ($attributes['tagsManager']) {
                $this->tagsManager = System::getContainer()->get('codefog_tags.manager_registry')->get($attributes['tagsManager']);
            }

            unset($attributes['tagsManager']);
        }

        parent::addAttributes($attributes);
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

        $template = new BackendTemplate('be_cfg_tags_widget');
        $template->name = $this->strName;
        $template->id = $this->strId;
        $template->hideList = $this->hideList ? true : false;
        $template->valueTags = $this->generateValueTags($this->getValueTags());
        $template->allTags = $this->generateAllTags($this->getAllTags());
        $template->config = $this->generateConfig();

        return $template->parse();
    }

    #[\Override]
    protected function getPost($key)
    {
        return array_filter(StringUtil::trimsplit(',', parent::getPost($key)));
    }

    /**
     * Generate the widget configuration.
     */
    protected function generateConfig(): array
    {
        $config = [
            'addLabel' => $GLOBALS['TL_LANG']['MSC']['cfg_tags.add'],
            'allowCreate' => isset($this->tagsCreate) ? (bool) $this->tagsCreate : true,
            'sortable' => isset($this->tagsSortable) && (bool) $this->tagsSortable,
        ];

        // Maximum number of items
        if (isset($this->maxItems)) {
            $config['maxItems'] = (int) $this->maxItems;
        }

        return $config;
    }

    /**
     * Get all tags.
     */
    protected function getAllTags(): array
    {
        return $this->tagsManager->getAllTags($this->tagsSource);
    }

    /**
     * Get the value tags.
     */
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

    /**
     * Generate the value tags.
     */
    private function generateValueTags(array $tags): array
    {
        $return = [];

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $return[] = $tag->getValue();
        }

        return $return;
    }

    /**
     * Generate all tags.
     */
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
