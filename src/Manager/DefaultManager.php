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

use Codefog\TagsBundle\Finder\SourceCriteria;
use Codefog\TagsBundle\Finder\SourceFinder;
use Codefog\TagsBundle\Finder\TagCriteria;
use Codefog\TagsBundle\Finder\TagFinder;
use Codefog\TagsBundle\Model\TagModel;
use Codefog\TagsBundle\Tag;
use Contao\DataContainer;
use Contao\StringUtil;

class DefaultManager implements ManagerInterface, DcaAwareInterface, InsertTagsAwareInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $sourceTable;

    /**
     * @var string
     */
    protected $sourceField;

    /**
     * @var TagFinder
     */
    protected $tagFinder;

    /**
     * @var SourceFinder
     */
    protected $sourceFinder;

    /**
     * DefaultManager constructor.
     */
    public function __construct(string $name, string $sourceTable, string $sourceField)
    {
        $this->name = $name;
        $this->sourceTable = $sourceTable;
        $this->sourceField = $sourceField;
    }

    /**
     * Set the tag finder
     *
     * @param TagFinder $tagFinder
     */
    public function setTagFinder(TagFinder $tagFinder): void
    {
        $this->tagFinder = $tagFinder;
    }

    /**
     * Set the source finder
     *
     * @param SourceFinder $sourceFinder
     */
    public function setSourceFinder(SourceFinder $sourceFinder): void
    {
        $this->sourceFinder = $sourceFinder;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleTags(array $values = []): array
    {
        $criteria = $this->createTagCriteria();
        $criteria->setValues($values);

        return $this->tagFinder->findMultiple($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function updateDcaField(array &$config): void
    {
        // Set the relation
        $config['relation'] = array_merge(
            (isset($config['relation']) && \is_array($config['relation'])) ? $config['relation'] : [],
            ['type' => 'haste-ManyToMany', 'load' => 'lazy', 'table' => 'tl_cfg_tag']
        );

        // Set the save_callback
        if (isset($config['save_callback']) && \is_array($config['save_callback'])) {
            array_unshift($config['save_callback'], ['codefog_tags.listener.tag_manager', 'onFieldSave']);
        } else {
            $config['save_callback'][] = ['codefog_tags.listener.tag_manager', 'onFieldSave'];
        }

        // Set the options_callback
        if (!isset($config['options_callback'])) {
            $config['options_callback'] = ['codefog_tags.listener.tag_manager', 'onOptionsCallback'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveDcaField(string $value, DataContainer $dc): string
    {
        $value = StringUtil::deserialize($value, true);
        $criteria = $this->createTagCriteria();

        /** @var array $value */
        foreach ($value as $k => $v) {
            // Do not create tags that already exist
            if (null !== $this->tagFinder->findSingle($criteria->setValue((string) $v))) {
                continue;
            }

            $model = new TagModel();
            $model->tstamp = time();
            $model->name = $v;
            $model->source = $this->name;
            $model->save();

            $value[$k] = $this->tagFinder->createTagFromModel($model)->getValue();
        }

        return serialize($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterOptions(DataContainer $dc): array
    {
        $options = [];

        /** @var Tag $tag */
        foreach ($this->tagFinder->findMultiple($this->createTagCriteria()->setUsedOnly(true)) as $tag) {
            $options[$tag->getValue()] = $tag->getName();
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceRecordsCount(array $data, DataContainer $dc): int
    {
        if (null === ($tag = $this->tagFinder->findSingle($this->createTagCriteria()->setValue((string) $data['id'])))) {
            return 0;
        }

        return $this->sourceFinder->count($this->createSourceCriteria()->setTag($tag));
    }

    /**
     * {@inheritdoc}
     */
    public function getInsertTagValue(string $value, string $property, array $elements): string
    {
        if (null === ($tag = $this->tagFinder->findSingle($this->createTagCriteria()->setValue($value)))) {
            return '';
        }

        if ('name' === $property) {
            return $tag->getName();
        }

        $data = $tag->getData();

        return isset($data[$property]) ? (string) $data[$property] : '';
    }

    /**
     * Get the tag finder.
     */
    public function getTagFinder(): TagFinder
    {
        return $this->tagFinder;
    }

    /**
     * Create the tag criteria.
     */
    public function createTagCriteria(): TagCriteria
    {
        return new TagCriteria($this->name, $this->sourceTable, $this->sourceField);
    }

    /**
     * Get the source finder.
     */
    public function getSourceFinder(): SourceFinder
    {
        return $this->sourceFinder;
    }

    /**
     * Create the source criteria.
     */
    public function createSourceCriteria(): SourceCriteria
    {
        return new SourceCriteria($this->name, $this->sourceTable, $this->sourceField);
    }
}
