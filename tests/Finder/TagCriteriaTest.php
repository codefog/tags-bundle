<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Test\Finder;

use Codefog\TagsBundle\Finder\TagCriteria;
use PHPUnit\Framework\TestCase;

class TagCriteriaTest extends TestCase
{
    public function testAll(): void
    {
        $criteria = new TagCriteria('my_manager', 'tl_table.tags');

        $this->assertSame('my_manager', $criteria->getName());
        $this->assertSame('tl_table.tags', $criteria->getSource());
        $this->assertSame('tl_table', $criteria->getSourceTable());
        $this->assertSame('tags', $criteria->getSourceField());
        $this->assertSame('name', $criteria->getOrder());
        $this->assertSame([], $criteria->getAliases());
        $this->assertSame([], $criteria->getSourceIds());
        $this->assertSame([], $criteria->getValues());
        $this->assertFalse($criteria->isUsedOnly());

        $criteria->setAliases(['foo', 'foo', 'bar']);
        $this->assertSame(['foo', 'bar'], $criteria->getAliases());

        $criteria->setAlias('foo');
        $this->assertSame(['foo'], $criteria->getAliases());

        $criteria->setSourceIds([1, 1, 2, 3]);
        $this->assertSame([1, 2, 3], $criteria->getSourceIds());

        $criteria->setValues(['foo', 'foo', 'bar', 'baz']);
        $this->assertSame(['foo', 'bar', 'baz'], $criteria->getValues());

        $criteria->setValue('foobar');
        $this->assertSame(['foobar'], $criteria->getValues());

        $criteria->setUsedOnly(true);
        $this->assertTrue($criteria->isUsedOnly());

        $criteria->setOrder('alias');
        $this->assertSame('alias', $criteria->getOrder());
    }
}
