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

use Doctrine\DBAL\Connection;
use Haste\Model\Model;
use Haste\Model\Relations;

class SourceFinder
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * DefaultFinder constructor.
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Count source records by criteria.
     */
    public function count(SourceCriteria $criteria): int
    {
        return \count($this->findMultiple($criteria));
    }

    /**
     * Find multiple source record IDs by criteria.
     */
    public function findMultiple(SourceCriteria $criteria): array
    {
        $ids = $criteria->getTagValues();

        // Collect tag IDs
        foreach ($criteria->getTags() as $tag) {
            $ids[] = $tag->getValue();
        }

        $values = Model::getReferenceValues($criteria->getSourceTable(), $criteria->getSourceField(), $ids);
        $values = array_values(array_unique($values));

        return array_map('intval', $values);
    }

    /**
     * Find the related source records.
     *
     * @throws \RuntimeException
     */
    public function findRelatedSourceRecords(SourceCriteria $criteria, int $limit = null): array
    {
        if (0 === \count($criteria->getIds())) {
            throw new \RuntimeException('No IDs have been provided');
        }

        if (false === ($relation = Relations::getRelation($criteria->getSourceTable(), $criteria->getSourceField()))) {
            throw new \RuntimeException(sprintf('The field %s.%s is not related', $criteria->getSourceTable(), $criteria->getSourceField()));
        }

        $tagIds = Model::getRelatedValues($criteria->getSourceTable(), $criteria->getSourceField(), $criteria->getIds());
        $tagIds = array_values(array_unique($tagIds));
        $tagIds = array_map('intval', $tagIds);

        if (0 === \count($tagIds)) {
            return [];
        }

        $query = sprintf(
            'SELECT %s, COUNT(*) AS relevance FROM %s WHERE %s IN (%s) AND %s NOT IN (%s) GROUP BY %s ORDER BY relevance DESC',
            $relation['reference_field'],
            $relation['table'],
            $relation['related_field'],
            implode(',', $tagIds),
            $relation['reference_field'],
            implode(',', $criteria->getIds()),
            $relation['reference_field']
        );

        // Set the limit
        if ($limit > 0) {
            $query .= sprintf(' LIMIT %s', $limit);
        }

        $related = [];
        $records = $this->db->fetchAllAssociative($query);

        // Generate the related records
        foreach ($records as $record) {
            $related[$record[$relation['reference_field']]] = [
                'total' => \count($tagIds),
                'found' => $record['relevance'],
                'prcnt' => ($record['relevance'] / \count($tagIds)) * 100,
            ];
        }

        return $related;
    }
}
