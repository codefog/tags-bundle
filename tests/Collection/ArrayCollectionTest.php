<?php

namespace Codefog\TagsBundle\Test\Collection;

use Codefog\TagsBundle\Collection\ArrayCollection;
use PHPUnit\Framework\TestCase;

class ArrayCollectionTest extends TestCase
{
    /**
     * @var ArrayCollection
     */
    private $collection;

    public function setUp()
    {
        $this->collection = new ArrayCollection(['tag1', 'tag2']);
    }

    public function testInstantiation()
    {
        static::assertInstanceOf(ArrayCollection::class, $this->collection);
    }

    public function testCount()
    {
        static::assertEquals(2, $this->collection->count());
    }

    public function testGetIterator()
    {
        static::assertInstanceOf(\ArrayIterator::class, $this->collection->getIterator());
    }
}
