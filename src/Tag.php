<?php

declare(strict_types=1);

namespace Codefog\TagsBundle;

class Tag
{
    /**
     * Tag constructor.
     */
    public function __construct(
        /**
         * Tag ID.
         */
        private string $value,
        /**
         * Tag name.
         */
        private string $name,
        /**
         * Data.
         */
        private array $data = [],
    ) {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
