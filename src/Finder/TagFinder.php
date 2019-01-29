<?php

namespace Codefog\TagsBundle\Finder;

use Codefog\TagsBundle\Exception\NoTagsException;
use Codefog\TagsBundle\Model\TagModel;
use Codefog\TagsBundle\Tag;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Doctrine\DBAL\Connection;
use Haste\Model\Model;
use Haste\Model\Relations;

class TagFinder
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * DefaultFinder constructor.
     *
     * @param Connection $db
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(Connection $db, ContaoFrameworkInterface $framework)
    {
        $this->db = $db;
        $this->framework = $framework;
    }

    /**
     * Count tags by criteria
     *
     * @param TagCriteria $criteria
     *
     * @return int
     */
    public function count(TagCriteria $criteria): int
    {
        try {
            list($columns, $values, $options) = $this->parseCriteria($criteria);
        } catch (NoTagsException $e) {
            return 0;
        }

        if (\count($columns) === 0) {
            return TagModel::countAll();
        }

        return TagModel::countBy($columns, $values, $options);
    }

    /**
     * Find a single tag by criteria
     *
     * @param TagCriteria $criteria
     *
     * @return Tag|null
     */
    public function findSingle(TagCriteria $criteria): ?Tag
    {
        try {
            list($columns, $values, $options) = $this->parseCriteria($criteria);
        } catch (NoTagsException $e) {
            return null;
        }

        if (\count($columns) === 0 || ($model = TagModel::findOneBy($columns, $values, $options)) === null) {
            return null;
        }

        return $this->createTagFromModel($model);
    }

    /**
     * Find multiple tags by criteria
     *
     * @param TagCriteria $criteria
     *
     * @return array
     */
    public function findMultiple(TagCriteria $criteria): array
    {
        try {
            list($columns, $values, $options) = $this->parseCriteria($criteria);
        } catch (NoTagsException $e) {
            return [];
        }

        if (\count($columns) === 0) {
            $models = TagModel::findAll($options);
        } else {
            $models = TagModel::findBy($columns, $values, $options);
        }

        if ($models === null) {
            return [];
        }

        $tags = [];

        /** @var TagModel $model */
        foreach ($models as $model) {
            $tags[] = $this->createTagFromModel($model);
        }

        return $tags;
    }

    /**
     * Get the top tag IDs.
     *
     * @param TagCriteria $criteria
     * @param int|null $limit
     * @param bool     $withCount
     *
     * @return array
     */
    public function getTopTagIds(TagCriteria $criteria, int $limit = null, bool $withCount = false): array
    {
        /** @var Model $model */
        $model = $this->framework->getAdapter(Model::class);

        // No array_unique() here!
        $tagIds = $model->getRelatedValues($criteria->getSourceTable(), $criteria->getSourceField(), $criteria->getSourceIds());
        $tagIds = \array_map('intval', $tagIds);

        if (0 === \count($tagIds)) {
            return [];
        }

        $helper = [];

        // Create the helper array with tag occurrences
        foreach ($tagIds as $tagId) {
            ++$helper[$tagId];
        }

        // Sort the helper array descending
        \arsort($helper);

        // Strip the count data
        if (!$withCount) {
            $helper = \array_keys($helper);
        }

        return \array_slice($helper, 0, $limit, $withCount);
    }

    /**
     * Create tag from model
     *
     * @param TagModel $model
     *
     * @return Tag
     */
    public function createTagFromModel(TagModel $model): Tag
    {
        return new Tag((string) $model->id, $model->name, $model->row());
    }

    /**
     * Parse the criteria to object
     *
     * @param TagCriteria $criteria
     *
     * @return array
     *
     * @throws NoTagsException
     */
    protected function parseCriteria(TagCriteria $criteria): array
    {
        $columns = ['source=?'];
        $values = [$criteria->getName()];
        $options = ['order' => 'name'];

        // Find the tags by single or multiple values
        if (count($ids = $criteria->getValues()) > 0) {
            if (count($ids) === 1) {
                $columns[] = 'id=?';
                $values[] = (int) $ids[0];
            } else {
                $columns[] = 'id IN ('.\implode(',', \array_map('intval', $ids)).')';
            }
        }

        // Find by single or multiple aliases
        if (count($aliases = $criteria->getAliases()) > 0) {
            if (count($aliases) === 1) {
                $columns[] = 'alias=?';
                $values[] = $aliases[0];
            } else {
                $columns[] = "alias IN ('".\implode("','", $aliases)."')'";
            }
        }

        // Find by source IDs
        if (count($sourceIds = $criteria->getSourceIds()) > 0) {
            /** @var Model $model */
            $model = $this->framework->getAdapter(Model::class);

            $ids = $model->getRelatedValues($criteria->getSourceTable(), $criteria->getSourceField(), $sourceIds);
            $ids = \array_values(\array_unique($ids));
            $ids = \array_map('intval', $ids);

            if (\count($ids) === 0) {
                throw new NoTagsException();
            }

            $columns[] = 'id IN ('.\implode(',', $ids).')';

            // Do not execute the same query once again
            $criteria->setUsedOnly(false);
        }

        // Find only the used tags
        if ($criteria->isUsedOnly()) {
            /** @var Model $model */
            $model = $this->framework->getAdapter(Model::class);

            $ids = $model->getRelatedValues($criteria->getSourceTable(), $criteria->getSourceField());
            $ids = \array_values(\array_unique($ids));
            $ids = \array_map('intval', $ids);

            if (\count($ids) === 0) {
                throw new NoTagsException();
            }

            $columns[] = 'id IN ('.\implode(',', $ids).')';
        }

        return [$columns, $values, $options];
    }
}
