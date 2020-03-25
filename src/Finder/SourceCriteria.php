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
    protected $source;

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
    public function __construct(string $name, string $source)
    {
        $this->name = $name;
        $this->source = $source;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getSourceTable(): string
    {
        return explode('.', $this->source)[0];
    }

    public function getSourceField(): string
    {
        return explode('.', $this->source, 2)[1];
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
