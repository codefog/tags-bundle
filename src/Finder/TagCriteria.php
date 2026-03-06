<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Finder;

class TagCriteria
{
    /**
     * @var array<string>
     */
    protected array $aliases = [];

    /**
     * @var array<int>
     */
    protected array $sourceIds = [];

    protected bool $usedOnly = false;

    /**
     * @var array<int|string>
     */
    protected array $values = [];

    protected string $order = 'name';

    public function __construct(
        protected string $name,
        protected string $source,
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

    /**
     * @return array<string>
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param array<string> $aliases
     */
    public function setAliases(array $aliases): self
    {
        $this->aliases = array_values(array_unique($aliases));

        return $this;
    }

    public function setAlias(string $alias): self
    {
        $this->aliases = [$alias];

        return $this;
    }

    /**
     * @return array<int>
     */
    public function getSourceIds(): array
    {
        return $this->sourceIds;
    }

    /**
     * @param array<int> $sourceIds
     */
    public function setSourceIds(array $sourceIds): self
    {
        $this->sourceIds = array_values(array_unique($sourceIds));

        return $this;
    }

    public function isUsedOnly(): bool
    {
        return $this->usedOnly;
    }

    public function setUsedOnly(bool $usedOnly): self
    {
        $this->usedOnly = $usedOnly;

        return $this;
    }

    /**
     * @return array<int|string>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array<int|string> $values
     */
    public function setValues(array $values): self
    {
        $this->values = array_values(array_unique($values));

        return $this;
    }

    public function setValue(string $value): self
    {
        $this->values = [$value];

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): self
    {
        $this->order = $order;

        return $this;
    }
}
