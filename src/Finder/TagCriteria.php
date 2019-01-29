<?php

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
    protected $sourceTable;

    /**
     * @var string
     */
    protected $sourceField;

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
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param array $aliases
     *
     * @return TagCriteria
     */
    public function setAliases(array $aliases): TagCriteria
    {
        $this->aliases = array_unique($aliases);

        return $this;
    }

    /**
     * @param string $alias
     *
     * @return TagCriteria
     */
    public function setAlias(string $alias): TagCriteria
    {
        $this->aliases = [$alias];

        return $this;
    }

    /**
     * @return array
     */
    public function getSourceIds(): array
    {
        return $this->sourceIds;
    }

    /**
     * @param array $sourceIds
     *
     * @return TagCriteria
     */
    public function setSourceIds(array $sourceIds): TagCriteria
    {
        $this->sourceIds = array_unique($sourceIds);

        return $this;
    }

    /**
     * @return bool
     */
    public function isUsedOnly(): bool
    {
        return $this->usedOnly;
    }

    /**
     * @param bool $usedOnly
     *
     * @return TagCriteria
     */
    public function setUsedOnly(bool $usedOnly): TagCriteria
    {
        $this->usedOnly = $usedOnly;

        return $this;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return array_unique($this->values);
    }

    /**
     * @param array $values
     *
     * @return TagCriteria
     */
    public function setValues(array $values): TagCriteria
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return TagCriteria
     */
    public function setValue(string $value): TagCriteria
    {
        $this->values = [$value];

        return $this;
    }
}
