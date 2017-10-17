<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Widget;

use Codefog\TagsBundle\Collection\CollectionInterface;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\Tag;
use Contao\BackendTemplate;
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

    /**
     * {@inheritdoc}
     */
    public function addAttributes($attributes = null)
    {
        if (is_array($attributes)) {
            if ($attributes['tagsManager']) {
                $this->tagsManager = System::getContainer()->get('codefog_tags.manager_registry')->get(
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
    public function validate()
    {
        $value = $this->validator($this->getPost($this->strName));

        // Validate the maximum number of items
        if (is_array($value) && isset($this->maxItems) && count($value) > $this->maxItems) {
            $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['maxval'], $this->strLabel, $this->maxItems));
        }

        parent::validate();
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        if ($this->tagsManager === null) {
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

    /**
     * {@inheritdoc}
     */
    protected function getPost($key)
    {
        return array_filter(trimsplit(',', parent::getPost($key)));
    }

    /**
     * Generate the widget configuration.
     *
     * @return array
     */
    protected function generateConfig(): array
    {
        $config = [
            'addLabel' => $GLOBALS['TL_LANG']['MSC']['cfg_tags.add'],
            'allowCreate' => isset($this->tagsCreate) ? (bool) $this->tagsCreate : true,
        ];

        // Maximum number of items
        if (isset($this->maxItems)) {
            $config['maxItems'] = (int) $this->maxItems;
        }

        return $config;
    }

    /**
     * Get the value tags.
     *
     * @return CollectionInterface
     */
    protected function getValueTags(): CollectionInterface
    {
        return $this->tagsManager->findMultiple(['values' => is_array($this->varValue) ? $this->varValue : []]);
    }

    /**
     * Get all tags.
     *
     * @return CollectionInterface
     */
    protected function getAllTags(): CollectionInterface
    {
        return $this->tagsManager->findMultiple();
    }

    /**
     * Generate the value tags.
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
     * Generate all tags.
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
