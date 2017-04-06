<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle\Manager;

use Codefog\TagsBundle\Collection\CollectionInterface;
use Codefog\TagsBundle\Collection\ModelCollection;
use Codefog\TagsBundle\Model\TagModel;
use Codefog\TagsBundle\Tag;
use Contao\DataContainer;
use Contao\StringUtil;
use Haste\Model\Model;

class DefaultManager implements ManagerInterface, DcaAwareInterface
{
    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $sourceTable;

    /**
     * @var string
     */
    private $sourceField;

    /**
     * DefaultManager constructor.
     *
     * @param string $sourceTable
     * @param string $sourceField
     */
    public function __construct(string $sourceTable, string $sourceField)
    {
        $this->sourceTable = $sourceTable;
        $this->sourceField = $sourceField;
    }

    /**
     * @inheritDoc
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @inheritDoc
     */
    public function find(string $value, array $criteria = []): ?Tag
    {
        if (($model = TagModel::findByPk($value)) === null) {
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
     * @inheritDoc
     */
    public function findMultiple(array $criteria = []): CollectionInterface
    {
        return new ModelCollection(TagModel::findByCriteria($this->getCriteria($criteria)));
    }

    /**
     * @inheritDoc
     */
    public function countSourceRecords(Tag $tag): int
    {
        return count(Model::getReferenceValues($this->sourceTable, $this->sourceField, $tag->getValue()));
    }

    /**
     * @inheritDoc
     */
    public function getSourceRecords(Tag $tag): array
    {
        $values = Model::getReferenceValues($this->sourceTable, $this->sourceField, $tag->getValue());
        $values = array_unique($values);
        $values = array_map('intval', $values);

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function updateDcaField(array &$config): void
    {
        $config['relation'] = ['type' => 'haste-ManyToMany', 'load' => 'lazy', 'table' => TagModel::getTable()];

        if (is_array($config['save_callback'])) {
            array_unshift($config['save_callback'], ['cfg_tags.listener.tag_manager', 'onFieldSave']);
        } else {
            $config['save_callback'][] = ['cfg_tags.listener.tag_manager', 'onFieldSave'];
        }
    }

    /**
     * @inheritDoc
     */
    public function saveDcaField(string $value, DataContainer $dc): string
    {
        $value    = StringUtil::deserialize($value, true);
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
     * Create the tag
     *
     * @param string $value
     *
     * @return Tag
     */
    private function createTag(string $value): Tag
    {
        $model         = new TagModel();
        $model->tstamp = time();
        $model->name   = $value;
        $model->source = $this->alias;
        $model->save();

        return ModelCollection::createTagFromModel($model);
    }

    /**
     * Get the criteria with necessary data
     *
     * @param array $criteria
     *
     * @return array
     */
    private function getCriteria(array $criteria = []): array
    {
        $criteria['source']      = $this->alias;
        $criteria['sourceTable'] = $this->sourceTable;
        $criteria['sourceField'] = $this->sourceField;

        return $criteria;
    }
}
