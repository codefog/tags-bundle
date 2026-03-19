<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Test\Finder;

use Codefog\TagsBundle\Finder\SourceCriteria;
use Codefog\TagsBundle\Tag;
use PHPUnit\Framework\TestCase;

final class SourceCriteriaTest extends TestCase
{
    public function testAll(): void
    {
        $criteria = new SourceCriteria('my_manager', 'tl_table.tags');

        $this->assertSame('my_manager', $criteria->getName());
        $this->assertSame('tl_table.tags', $criteria->getSource());
        $this->assertSame('tl_table', $criteria->getSourceTable());
        $this->assertSame('tags', $criteria->getSourceField());
        $this->assertSame([], $criteria->getIds());

        $criteria->setIds([1, 2, 3]);
        $this->assertSame([1, 2, 3], $criteria->getIds());

        $criteria->setTags([$tag1 = new Tag('foo', 'bar'), $tag2 = new Tag('bar', 'foo')]);
        $this->assertSame([$tag1, $tag2], $criteria->getTags());

        $criteria->setTag($tag3 = new Tag('quux', 'quux'));
        $this->assertSame([$tag3], $criteria->getTags());

        $criteria->setTagValues(['foo', 'bar']);
        $this->assertSame(['foo', 'bar'], $criteria->getTagValues());

        $criteria->setTagValue('foobar');
        $this->assertSame(['foobar'], $criteria->getTagValues());
    }
}
