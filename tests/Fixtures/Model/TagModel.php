<?php

namespace Codefog\TagsBundle\Test\Fixtures\Model;

class TagModel
{
    private static $mapper = [];

    public function __construct(array $mapper = [])
    {
        static::$mapper = $mapper;
    }

    public function __get($key)
    {
        return '';
    }

    public static function getTable()
    {
        return static::$mapper['getTable'] ?? null;
    }

    public static function findByPk($varValue, array $arrOptions = [])
    {
        return static::$mapper['findByPk'] ?? null;
    }

    public static function findByCriteria()
    {
        return static::$mapper['findByCriteria'] ?? null;
    }

    public static function findOneByCriteria()
    {
        return static::$mapper['findOneByCriteria'] ?? null;
    }

    public function getReferenceValues()
    {
        return static::$mapper['getReferenceValues'] ?? null;
    }

    public function save()
    {
        return static::$mapper['save'] ?? null;
    }

    public function row()
    {
        return [];
    }
}
