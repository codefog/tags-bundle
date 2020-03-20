<?php

namespace Codefog\TagsBundle\Test\Finder;

use Codefog\TagsBundle\Finder\SourceCriteria;
use Codefog\TagsBundle\Tag;
use PHPUnit\Framework\TestCase;

class SourceCriteriaTest extends TestCase
{
    public function testAll()
    {
        $criteria = new SourceCriteria('my_manager', 'tl_table', 'tags');

        $this->assertEquals('my_manager', $criteria->getName());
        $this->assertEquals('tl_table', $criteria->getSourceTable());
        $this->assertEquals('tags', $criteria->getSourceField());
        $this->assertEquals([], $criteria->getIds());

        $criteria->setIds([1, 2, 3]);
        $this->assertEquals([1, 2, 3], $criteria->getIds());

        $criteria->setTags([$tag1 = new Tag('foo', 'bar'), $tag2 = new Tag('bar', 'foo')]);
        $this->assertEquals([$tag1, $tag2], $criteria->getTags());

        $criteria->setTag($tag3 = new Tag('quux', 'quux'));
        $this->assertEquals([$tag3], $criteria->getTags());
    }
}
