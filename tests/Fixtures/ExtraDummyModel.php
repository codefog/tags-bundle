<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Test\Fixtures;

class ExtraDummyModel extends \Contao\Model
{
    private static $mapper = [];

    public function __construct(array $mapper = [])
    {
        static::$mapper = $mapper;
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

    public function getReferenceValues()
    {
        return static::$mapper['getReferenceValues'];
    }

    public function save()
    {
        return static::$mapper['save'];
    }
}
