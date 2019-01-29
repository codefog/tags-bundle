<?php

namespace Codefog\TagsBundle\Finder;

use Codefog\TagsBundle\Tag;

class SourceCriteria
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
     * @var array
     */
    protected $ids = [];

    /**
     * @var Tag[]
     */
    protected $tags = [];

    /**
     * Criteria constructor.
     *
     * @param string $name
     * @param string $sourceTable
     * @param string $sourceField
     */
    public function __construct(string $name, string $sourceTable, string $sourceField)
    {
        $this->name = $name;
        $this->sourceTable = $sourceTable;
        $this->sourceField = $sourceField;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSourceTable(): string
    {
        return $this->sourceTable;
    }

    /**
     * @return string
     */
    public function getSourceField(): string
    {
        return $this->sourceField;
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     *
     * @return SourceCriteria
     */
    public function setIds(array $ids): SourceCriteria
    {
        $this->ids = $ids;

        return $this;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param Tag[] $tags
     *
     * @return SourceCriteria
     */
    public function setTags(array $tags): SourceCriteria
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param Tag $tag
     *
     * @return SourceCriteria
     */
    public function setTag(Tag $tag): SourceCriteria
    {
        $this->tags = [$tag];

        return $this;
    }
}
