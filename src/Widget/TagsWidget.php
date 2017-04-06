<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle\Widget;

use Codefog\TagsBundle\Collection\CollectionInterface;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\Tag;
use Contao\BackendTemplate;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;

class TagsWidget extends Widget
{
    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Add a for attribute
     * @var boolean
     */
    protected $blnForAttribute = true;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Tags manager
     * @var ManagerInterface
     */
    protected $tagsManager;

    /**
     * @inheritDoc
     */
    public function addAttributes($attributes = null)
    {
        if (is_array($attributes)) {
            if ($attributes['tagsManager']) {
                $this->tagsManager = System::getContainer()->get('cfg_tags.manager_registry')->get(
                    $attributes['tagsManager']
                );
            }

            unset($attributes['tagsManager']);
        }

        return parent::addAttributes($attributes);
    }

    /**
     * @inheritDoc
     */
    public function __set($key, $value)
    {
        switch ($key) {
            case 'options':
                $this->arrOptions = StringUtil::deserialize($value);
                break;
        }

        parent::__set($key, $value);
    }

    /**
     * @inheritDoc
     */
    protected function getPost($key)
    {
        return array_filter(trimsplit(',', parent::getPost($key)));
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        if ($this->tagsManager === null) {
            return '';
        }

        $template            = new BackendTemplate('be_cfg_tags_widget');
        $template->name      = $this->strName;
        $template->id        = $this->strId;
        $template->valueTags = $this->generateValueTags($this->getValueTags());
        $template->allTags   = $this->generateAllTags($this->getAllTags());

        return $template->parse();
    }

    /**
     * Get the value tags
     *
     * @return CollectionInterface
     */
    protected function getValueTags(): CollectionInterface
    {
        return $this->tagsManager->findMultiple(['values' => $this->varValue]);
    }

    /**
     * Get all tags
     *
     * @return CollectionInterface
     */
    protected function getAllTags(): CollectionInterface
    {
        return $this->tagsManager->findMultiple();
    }

    /**
     * Generate the value tags
     *
     * @param CollectionInterface $tags
     *
     * @return array
     */
    private function generateValueTags(CollectionInterface $tags): array
    {
        $return = [];

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $return[] = $tag->getValue();
        }

        return $return;
    }

    /**
     * Generate all tags
     *
     * @param CollectionInterface $tags
     *
     * @return array
     */
    private function generateAllTags(CollectionInterface $tags): array
    {
        $return = [];

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $return[] = ['value' => $tag->getValue(), 'text' => $tag->getName()];
        }

        return $return;
    }
}
