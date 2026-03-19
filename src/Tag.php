<?php

declare(strict_types=1);

namespace Codefog\TagsBundle;

class Tag
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private string $value,
        private string $name,
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

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
