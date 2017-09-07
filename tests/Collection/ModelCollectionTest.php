<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Test\Collection;

use Codefog\TagsBundle\Collection\ModelCollection;
use Codefog\TagsBundle\Test\Fixtures\DummyModel;
use Contao\Model\Collection;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../Fixtures/DcaExtractor.php';
require_once __DIR__.'/../Fixtures/Model.php';

class ModelCollectionTest extends TestCase
{
    /**
     * @var ModelCollection
     */
    private $collection;

    public function setUp()
    {
        // Adjust the error reporting because of the Contao\Model
        error_reporting(E_ALL & ~E_NOTICE);

        $dummyModel = new DummyModel();
        $dummyModel->id = 123;
        $dummyModel->name = 'foobar';

        $this->collection = new ModelCollection(new Collection([$dummyModel], 'tl_table'));
    }

    public function testInstantiation()
    {
        static::assertInstanceOf(ModelCollection::class, $this->collection);
    }

    public function testCount()
    {
        static::assertEquals(1, $this->collection->count());
    }

    public function testGetIterator()
    {
        static::assertInstanceOf(\ArrayIterator::class, $this->collection->getIterator());
    }
}
