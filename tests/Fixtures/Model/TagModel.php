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
        return static::$mapper['getTable'];
    }

    public static function findByPk($varValue, array $arrOptions = [])
    {
        return static::$mapper['findByPk'];
    }

    public static function findByCriteria()
    {
        return static::$mapper['findByCriteria'];
    }

    public static function findOneByCriteria()
    {
        return static::$mapper['findOneByCriteria'];
    }

    public function getReferenceValues()
    {
        return static::$mapper['getReferenceValues'];
    }

    public function save()
    {
        return static::$mapper['save'];
    }

    public function row()
    {
        return [];
    }
}
