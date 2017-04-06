<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle\Model;

use Codefog\TagsBundle\Exception\NoTagsException;
use Contao\Model;

class TagModel extends Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_cfg_tag';

    /**
     * Find the records by criteria
     *
     * @param array $criteria
     *
     * @return Model\Collection|null
     */
    public static function findByCriteria(array $criteria): ?Model\Collection
    {
        try {
            list($columns, $values, $options) = static::parseCriteria($criteria);
        } catch (NoTagsException $e) {
            return null;
        }

        if (count($columns) < 1) {
            return static::findAll($options);
        }

        return static::findBy($columns, $values, $options);
    }

    /**
     * Parse the criteria
     *
     * @param array $criteria
     *
     * @return array
     *
     * @throws NoTagsException
     */
    private static function parseCriteria(array $criteria):array
    {
        $columns = [];
        $values  = [];
        $options = ['order' => 'name'];

        // Find by source
        if ($criteria['source']) {
            $columns[] = 'source=?';
            $values[]  = $criteria['source'];
        }

        // Find only the used tags
        if ($criteria['usedOnly']) {
            $ids = \Haste\Model\Model::getRelatedValues($criteria['sourceTable'], $criteria['sourceField']);

            if (count($ids) < 1) {
                throw new NoTagsException;
            }

            $columns[] = 'id IN ('.implode(',', array_map('intval', array_unique($ids))).')';
        }

        // Find the tags by values/IDs
        if (is_array($criteria['values'])) {
            if (count($criteria['values']) < 1) {
                throw new NoTagsException;
            }

            $columns[] = 'id IN ('.implode(',', array_map('intval', $criteria['values'])).')';
        }

        return [$columns, $values, $options];
    }
}
