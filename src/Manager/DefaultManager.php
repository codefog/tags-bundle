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
     * @var array
     */
    protected $sources;

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
    public function __construct(string $name, array $sources)
    {
        $this->name = $name;
        $this->sources = $sources;
    }

    /**
     * Set the tag finder.
     */
    public function setTagFinder(TagFinder $tagFinder): void
    {
        $this->tagFinder = $tagFinder;
    }

    /**
     * Set the source finder.
     */
    public function setSourceFinder(SourceFinder $sourceFinder): void
    {
        $this->sourceFinder = $sourceFinder;
    }

    /**
     * @inheritDoc
     */
    public function getAllTags(string $source = null): array
    {
        return $this->tagFinder->findMultiple($this->createTagCriteria($source));
    }

    /**
     * @inheritDoc
     */
    public function getFilteredTags(array $values, string $source = null): array
    {
        $criteria = $this->createTagCriteria($source);
        $criteria->setValues(\count($values) ? $values : [0]);

        return $this->tagFinder->findMultiple($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function updateDcaField(string $table, string $field, array &$config): void
    {
        $config['eval']['tagsCreate'] = $config['eval']['tagsCreate'] ?? true;

        // Set the relation
        if (!isset($config['sql'])) {
            $config['relation'] = array_merge(
                (isset($config['relation']) && \is_array($config['relation'])) ? $config['relation'] : [],
                ['type' => 'haste-ManyToMany', 'load' => 'lazy', 'table' => 'tl_cfg_tag']
            );
        }

        // Set the save_callback
        if ($config['eval']['tagsCreate']) {
            if (isset($config['save_callback']) && \is_array($config['save_callback'])) {
                array_unshift($config['save_callback'], ['codefog_tags.listener.tag_manager', 'onFieldSaveCallback']);
            } else {
                $config['save_callback'][] = ['codefog_tags.listener.tag_manager', 'onFieldSaveCallback'];
            }
        }

        // Set the options_callback
        if (!isset($config['options_callback'])) {
            $config['options_callback'] = ['codefog_tags.listener.tag_manager', 'onOptionsCallback'];
        }

        // Set the tag source
        if (!isset($config['eval']['tagsSource'])) {
            $config['eval']['tagsSource'] = $table.'.'.$field;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveDcaField(string $value, DataContainer $dc): string
    {
        $value = StringUtil::deserialize($value, true);
        $source = $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['tagsSource'] ?? null;
        $criteria = $this->createTagCriteria($source);

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

        if (isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['tagsSource'])) {
            $sources = [$GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['tagsSource']];
        } else {
            $sources = $this->sources;
        }

        foreach ($sources as $source) {
            /** @var Tag $tag */
            foreach ($this->tagFinder->findMultiple($this->createTagCriteria($source)->setUsedOnly(true)) as $tag) {
                $options[$tag->getValue()] = $tag->getName();
            }
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceRecordsCount(array $data, DataContainer $dc): int
    {
        if (isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['tagsSource'])) {
            $sources = [$GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['tagsSource']];
        } else {
            $sources = $this->sources;
        }

        $total = 0;

        foreach ($sources as $source) {
            if (null === ($tag = $this->tagFinder->findSingle($this->createTagCriteria($source)->setValue((string) $data['id'])))) {
                continue;
            }

            $total += $this->sourceFinder->count($this->createSourceCriteria($source)->setTag($tag));
        }

        return $total;
    }

    /**
     * {@inheritdoc}
     */
    public function getTopTagIds(string $source = null): array
    {
        $ids = [];
        $sources = (null !== $source) ? [$source] : $this->sources;

        foreach ($sources as $source) {
            foreach ($this->getTagFinder()->getTopTagIds($this->createTagCriteria($source), null, true) as $id => $count) {
                if (!isset($ids[$id])) {
                    $ids[$id] = $count;
                } else {
                    $ids[$id] += $count;
                }
            }
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function getInsertTagValue(string $value, string $property, array $elements): string
    {
        $tag = null;

        // Find the tag in all sources
        foreach ($this->sources as $source) {
            if (null !== ($tag = $this->tagFinder->findSingle($this->createTagCriteria($source)->setValue($value)))) {
                break;
            }
        }

        if (null === $tag) {
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
    public function createTagCriteria(string $source = null): TagCriteria
    {
        return new TagCriteria($this->name, $this->getSource($source));
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
    public function createSourceCriteria(string $source = null): SourceCriteria
    {
        return new SourceCriteria($this->name, $this->getSource($source));
    }

    /**
     * Get the source.
     */
    protected function getSource(string $source = null): string
    {
        if (null === $source) {
            $source = $this->sources[0];
        } elseif (!\in_array($source, $this->sources, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid source "%s"!', $source));
        }

        return $source;
    }
}
