<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle;

class Tag
{
    /**
     * Tag ID.
     *
     * @var string
     */
    private $value;

    /**
     * Tag name.
     *
     * @var string
     */
    private $name;

    /**
     * Data.
     *
     * @var array
     */
    private $data;

    /**
     * Tag constructor.
     */
    public function __construct(string $value, string $name, array $data = [])
    {
        $this->value = $value;
        $this->name = $name;
        $this->data = $data;
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
