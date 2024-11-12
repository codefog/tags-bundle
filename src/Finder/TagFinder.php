<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Finder;

use Codefog\HasteBundle\Model\DcaRelationsModel;
use Codefog\TagsBundle\Exception\NoTagsException;
use Codefog\TagsBundle\Model\TagModel;
use Codefog\TagsBundle\Tag;

class TagFinder
{
    /**
     * Count tags by criteria.
     */
    public function count(TagCriteria $criteria): int
    {
        try {
            [$columns, $values, $options] = $this->parseCriteria($criteria);
        } catch (NoTagsException $e) {
            return 0;
        }

        if (0 === \count($columns)) {
            return TagModel::countAll();
        }

        return TagModel::countBy($columns, $values, $options);
    }

    /**
     * Find a single tag by criteria.
     */
    public function findSingle(TagCriteria $criteria): ?Tag
    {
        try {
            [$columns, $values, $options] = $this->parseCriteria($criteria);
        } catch (NoTagsException $e) {
            return null;
        }

        if (0 === \count($columns) || null === ($model = TagModel::findOneBy($columns, $values, $options))) {
            return null;
        }

        return $this->createTagFromModel($model);
    }

    /**
     * Find multiple tags by criteria.
     */
    public function findMultiple(TagCriteria $criteria): array
    {
        try {
            [$columns, $values, $options] = $this->parseCriteria($criteria);
        } catch (NoTagsException $e) {
            return [];
        }

        if (0 === \count($columns)) {
            $models = TagModel::findAll($options);
        } else {
            $models = TagModel::findBy($columns, $values, $options);
        }

        if (null === $models) {
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
     * Get the top tags. The tag count will be part of tag's data ($tag->getData()['count']).
     */
    public function getTopTags(TagCriteria $criteria, int $limit = null, bool $withCount = false): array
    {
        if (0 === \count($tagIds = $this->getTopTagIds($criteria, $limit, $withCount))) {
            return [];
        }

        $tags = $this->findMultiple($criteria->setValues($withCount ? array_keys($tagIds) : $tagIds));

        // Enhance the tags data with count
        if ($withCount) {
            /** @var Tag $tag */
            foreach ($tags as $tag) {
                $tag->setData(array_merge($tag->getData(), ['count' => $tagIds[$tag->getValue()]]));
            }
        }

        return $tags;
    }

    /**
     * Get the top tag IDs.
     */
    public function getTopTagIds(TagCriteria $criteria, int $limit = null, bool $withCount = false): array
    {
        // No array_unique() here!
        $tagIds = DcaRelationsModel::getRelatedValues($criteria->getSourceTable(), $criteria->getSourceField(), $criteria->getSourceIds());
        $tagIds = array_map('intval', $tagIds);

        if (0 === \count($tagIds)) {
            return [];
        }

        $helper = [];

        // Create the helper array with tag occurrences
        foreach ($tagIds as $tagId) {
            if (!isset($helper[$tagId])) {
                $helper[$tagId] = 0;
            }

            ++$helper[$tagId];
        }

        // Sort the helper array descending
        arsort($helper);

        // Strip the count data
        if (!$withCount) {
            $helper = array_keys($helper);
        }

        return \array_slice($helper, 0, $limit, $withCount);
    }

    /**
     * Create tag from model.
     */
    public function createTagFromModel(TagModel $model): Tag
    {
        return new Tag((string) $model->id, (string) $model->name, $model->row());
    }

    /**
     * Parse the criteria to object.
     *
     * @throws NoTagsException
     */
    protected function parseCriteria(TagCriteria $criteria): array
    {
        $columns = ['source=?'];
        $values = [$criteria->getName()];
        $options = ['order' => $criteria->getOrder()];

        // Find the tags by single or multiple values
        if (\count($ids = $criteria->getValues()) > 0) {
            if (1 === \count($ids)) {
                $columns[] = 'id=?';
                $values[] = (int) $ids[0];
            } else {
                $columns[] = 'id IN ('.implode(',', array_map('intval', $ids)).')';
            }
        }

        // Find by single or multiple aliases
        if (\count($aliases = $criteria->getAliases()) > 0) {
            if (1 === \count($aliases)) {
                $columns[] = 'alias=?';
                $values[] = $aliases[0];
            } else {
                $columns[] = "alias IN ('".implode("','", $aliases)."')";
            }
        }

        // Find by source IDs
        if (\count($sourceIds = $criteria->getSourceIds()) > 0) {
            $ids = DcaRelationsModel::getRelatedValues($criteria->getSourceTable(), $criteria->getSourceField(), $sourceIds);
            $ids = array_values(array_unique($ids));
            $ids = array_map('intval', $ids);

            if (0 === \count($ids)) {
                throw new NoTagsException();
            }

            $columns[] = 'id IN ('.implode(',', $ids).')';

            // Do not execute the same query once again
            $criteria->setUsedOnly(false);
        }

        // Find only the used tags
        if ($criteria->isUsedOnly()) {
            $ids = DcaRelationsModel::getRelatedValues($criteria->getSourceTable(), $criteria->getSourceField());
            $ids = array_values(array_unique($ids));
            $ids = array_map('intval', $ids);

            if (0 === \count($ids)) {
                throw new NoTagsException();
            }

            $columns[] = 'id IN ('.implode(',', $ids).')';
        }

        return [$columns, $values, $options];
    }
}
