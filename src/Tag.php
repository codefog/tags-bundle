<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
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
     *
     * @param string $value
     * @param string $name
     * @param array  $data
     */
    public function __construct(string $value, string $name, array $data = [])
    {
        $this->value = $value;
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
}
