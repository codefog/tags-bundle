<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Finder;

use Codefog\TagsBundle\Tag;

class SourceCriteria
{
    protected array $ids = [];

    /**
     * @var array<Tag>
     */
    protected array $tags = [];

    protected array $tagValues = [];

    public function __construct(
        protected readonly string $name,
        protected readonly string $source,
    ) {
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
     * @return array<Tag>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<Tag> $tags
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

    public function getTagValues(): array
    {
        return $this->tagValues;
    }

    public function setTagValues(array $tagValues): self
    {
        $this->tagValues = $tagValues;

        return $this;
    }

    public function setTagValue(string $tagValue): self
    {
        $this->tagValues = [$tagValue];

        return $this;
    }
}
