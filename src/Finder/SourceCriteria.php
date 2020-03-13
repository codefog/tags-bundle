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
     */
    public function __construct(string $name, string $sourceTable, string $sourceField)
    {
        $this->name = $name;
        $this->sourceTable = $sourceTable;
        $this->sourceField = $sourceField;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSourceTable(): string
    {
        return $this->sourceTable;
    }

    public function getSourceField(): string
    {
        return $this->sourceField;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function setIds(array $ids): self
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
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function setTag(Tag $tag): self
    {
        $this->tags = [$tag];

        return $this;
    }
}
