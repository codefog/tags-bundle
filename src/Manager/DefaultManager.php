<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Manager;

use Codefog\TagsBundle\Collection\CollectionInterface;
use Codefog\TagsBundle\Collection\ModelCollection;
use Codefog\TagsBundle\Model\TagModel;
use Codefog\TagsBundle\Tag;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\StringUtil;
use Haste\Model\Model;

class DefaultManager implements ManagerInterface, DcaAwareInterface, DcaFilterAwareInterface
{
    /**
     * @var string
     */
    protected $alias;

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var string
     */
    protected $sourceTable;

    /**
     * @var string
     */
    protected $sourceField;

    /**
     * DefaultManager constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param string                   $sourceTable
     * @param string                   $sourceField
     */
    public function __construct(ContaoFrameworkInterface $framework, string $sourceTable, string $sourceField)
    {
        $this->framework = $framework;
        $this->sourceTable = $sourceTable;
        $this->sourceField = $sourceField;
    }

    /**
     * {@inheritdoc}
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $value, array $criteria = []): ?Tag
    {
        /** @var TagModel $adapter */
        $adapter = $this->framework->getAdapter(TagModel::class);

        if (($model = $adapter->findByPk($value)) === null) {
            return null;
        }

        $criteria = $this->getCriteria($criteria);

        // Check the source
        if ($model->source !== $criteria['source']) {
            return null;
        }

        return ModelCollection::createTagFromModel($model);
    }

    /**
     * {@inheritdoc}
     */
    public function findMultiple(array $criteria = []): CollectionInterface
    {
        /** @var TagModel $adapter */
        $adapter = $this->framework->getAdapter(TagModel::class);

        return new ModelCollection($adapter->findByCriteria($this->getCriteria($criteria)));
    }

    /**
     * {@inheritdoc}
     */
    public function countSourceRecords(Tag $tag): int
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter(Model::class);

        return count($adapter->getReferenceValues($this->sourceTable, $this->sourceField, $tag->getValue()));
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceRecords(Tag $tag): array
    {
        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter(Model::class);

        $values = $adapter->getReferenceValues($this->sourceTable, $this->sourceField, $tag->getValue());
        $values = array_values(array_unique($values));
        $values = array_map('intval', $values);

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDcaField(array &$config): void
    {
        /** @var TagModel $adapter */
        $adapter = $this->framework->getAdapter(TagModel::class);

        // Set the relation
        $config['relation'] = array_merge(
            (isset($config['relation']) && is_array($config['relation'])) ? $config['relation'] : [],
            ['type' => 'haste-ManyToMany', 'load' => 'lazy', 'table' => $adapter->getTable()]
        );

        // Set the save_callback
        if (isset($config['save_callback']) && is_array($config['save_callback'])) {
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
        $criteria = $this->getCriteria();

        /** @var array $value */
        foreach ($value as $k => $v) {
            if ($this->find($v, $criteria) !== null) {
                continue;
            }

            $value[$k] = $this->createTag($v)->getValue();
        }

        return serialize($value);
    }

    /**
     * @inheritDoc
     */
    public function getFilterOptions(DataContainer $dc): array
    {
        $options = [];

        /** @var Model $adapter */
        $adapter = $this->framework->getAdapter(Model::class);

        $ids = array_unique($adapter->getRelatedValues($this->sourceTable, $this->sourceField));
        $tags = $this->findMultiple(['values' => $ids]);

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $options[$tag->getValue()] = $tag->getName();
        }

        return $options;
    }

    /**
     * Create the tag.
     *
     * @param string $value
     *
     * @return Tag
     */
    protected function createTag(string $value): Tag
    {
        /** @var TagModel $model */
        $model = $this->framework->createInstance(TagModel::class);
        $model->tstamp = time();
        $model->name = $value;
        $model->source = $this->alias;
        $model->save();

        return ModelCollection::createTagFromModel($model);
    }

    /**
     * Get the criteria with necessary data.
     *
     * @param array $criteria
     *
     * @return array
     */
    protected function getCriteria(array $criteria = []): array
    {
        $criteria['source'] = $this->alias;
        $criteria['sourceTable'] = $this->sourceTable;
        $criteria['sourceField'] = $this->sourceField;

        return $criteria;
    }
}
