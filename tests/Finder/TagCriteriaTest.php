<?php

namespace Codefog\TagsBundle\Test\Finder;

use Codefog\TagsBundle\Finder\TagCriteria;
use PHPUnit\Framework\TestCase;

class TagCriteriaTest extends TestCase
{
    public function testAll()
    {
        $criteria = new TagCriteria('my_manager', 'tl_table.tags');

        $this->assertEquals('my_manager', $criteria->getName());
        $this->assertEquals('tl_table.tags', $criteria->getSource());
        $this->assertEquals('tl_table', $criteria->getSourceTable());
        $this->assertEquals('tags', $criteria->getSourceField());
        $this->assertEquals([], $criteria->getAliases());
        $this->assertEquals([], $criteria->getSourceIds());
        $this->assertEquals([], $criteria->getValues());
        $this->assertFalse($criteria->isUsedOnly());

        $criteria->setAliases(['foo', 'foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $criteria->getAliases());

        $criteria->setAlias('foo');
        $this->assertEquals(['foo'], $criteria->getAliases());

        $criteria->setSourceIds([1, 1, 2, 3]);
        $this->assertEquals([1, 2, 3], $criteria->getSourceIds());

        $criteria->setValues(['foo', 'foo', 'bar', 'baz']);
        $this->assertEquals(['foo', 'bar', 'baz'], $criteria->getValues());

        $criteria->setValue('foobar');
        $this->assertEquals(['foobar'], $criteria->getValues());

        $criteria->setUsedOnly(true);
        $this->assertTrue($criteria->isUsedOnly());
    }
}
