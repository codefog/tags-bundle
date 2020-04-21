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

class TagCriteria
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
    protected $aliases = [];

    /**
     * @var array
     */
    protected $sourceIds = [];

    /**
     * @var bool
     */
    protected $usedOnly = false;

    /**
     * @var array
     */
    protected $values = [];

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

    public function getAliases(): array
    {
        return $this->aliases;
    }

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

    public function getSourceIds(): array
    {
        return $this->sourceIds;
    }

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

    public function getValues(): array
    {
        return $this->values;
    }

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
}
