<?php

namespace Codefog\TagsBundle\Test\Manager;

use Codefog\TagsBundle\Collection\CollectionInterface;
use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Model\TagModel;
use Codefog\TagsBundle\Tag;
use Codefog\TagsBundle\Test\Fixtures\DummyModel;
use Codefog\TagsBundle\Test\Fixtures\ExtraDummyModel;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Model\Collection;
use Doctrine\DBAL\Connection;
use Haste\Model\Model;
use Haste\Model\Relations;
use PHPUnit\Framework\TestCase;

class DefaultManagerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private $db;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var DefaultManager
     */
    private $manager;

    public function setUp()
    {
        // Adjust the error reporting because of the Contao\Model
        error_reporting(E_ALL & ~E_NOTICE);

        $this->framework = $this->createMock(ContaoFrameworkInterface::class);
        $this->db = $this->createMock(Connection::class);

        $this->manager = new DefaultManager($this->framework, 'tl_table', 'field');
        $this->manager->setAlias('foobar');
        $this->manager->setDatabase($this->db);
    }

    public function testInstantiation()
    {
        static::assertInstanceOf(DefaultManager::class, $this->manager);
    }

    public function testFind()
    {
        $dummyModel = new DummyModel();
        $dummyModel->id = 123;
        $dummyModel->name = 'foobar';
        $dummyModel->source = 'foobar';

        $this->framework->method('getAdapter')->willReturn(new DummyModel(['findByPk' => $dummyModel]));
        static::assertInstanceOf(Tag::class, $this->manager->find('123'));
    }

    public function testFindByAlias()
    {
        $dummyModel = new DummyModel();
        $dummyModel->id = 123;
        $dummyModel->name = 'foobar';
        $dummyModel->source = 'foobar';

        $this->framework->method('getAdapter')->willReturn(new DummyModel(['findOneByCriteria' => $dummyModel]));
        static::assertInstanceOf(Tag::class, $this->manager->findByAlias('foobar'));

        $this->framework->method('getAdapter')->willReturn(new DummyModel(['findOneByCriteria' => null]));
        static::assertNull($this->manager->findByAlias('foobar'));
    }

    public function testFindTagNotFound()
    {
        $this->framework->method('getAdapter')->willReturn(new DummyModel(['findByPk' => null]));
        static::assertNull($this->manager->find('123'));
    }

    public function testFindSourceDoesNotMatch()
    {
        $dummyModel = new DummyModel();
        $dummyModel->id = 123;
        $dummyModel->name = 'foobar';
        $dummyModel->source = 'foobaz';

        $this->framework->method('getAdapter')->willReturn(new DummyModel(['findByPk' => $dummyModel]));
        static::assertNull($this->manager->find('123'));
    }

    public function testFindMultiple()
    {
        $this->framework->method('getAdapter')->willReturn(new DummyModel(['findByCriteria' => null]));
        static::assertInstanceOf(CollectionInterface::class, $this->manager->findMultiple());
    }

    public function testCountSourceRecords()
    {
        $this->framework->method('getAdapter')->willReturn(new DummyModel(['getReferenceValues' => [1, 2, 3]]));
        static::assertEquals(3, $this->manager->countSourceRecords(new Tag('foo', 'bar')));
    }

    public function testGetSourceRecords()
    {
        $this->framework->method('getAdapter')->willReturn(new DummyModel(['getReferenceValues' => ['1', 2, 2, 3]]));
        static::assertEquals([1, 2, 3], $this->manager->getSourceRecords(new Tag('foo', 'bar')));
    }

    /**
     * @dataProvider updateDcaFieldProvider
     */
    public function testUpdateDcaField(array $provided, array $expected)
    {
        $this->framework->method('getAdapter')->willReturn(
            new DummyModel(['getTable' => 'tl_cfg_tag'])
        );

        $this->manager->updateDcaField($provided);

        static::assertEquals($expected, $provided);
    }

    public function updateDcaFieldProvider()
    {
        return [
            'Empty config' => [
                [],
                [
                    'relation' => [
                        'type' => 'haste-ManyToMany',
                        'load' => 'lazy',
                        'table' => 'tl_cfg_tag',
                    ],
                    'save_callback' => [
                        ['codefog_tags.listener.tag_manager', 'onFieldSave']
                    ],
                    'options_callback' => ['codefog_tags.listener.tag_manager', 'onOptionsCallback'],
                ]
            ],

            'Full config' => [
                [
                    'save_callback' => [
                        ['foo', 'bar'],
                    ],
                    'options_callback' => ['foo', 'bar'],
                ],
                [
                    'relation' => [
                        'type' => 'haste-ManyToMany',
                        'load' => 'lazy',
                        'table' => 'tl_cfg_tag',
                    ],
                    'save_callback' => [
                        ['codefog_tags.listener.tag_manager', 'onFieldSave'],
                        ['foo', 'bar'],
                    ],
                    'options_callback' => ['foo', 'bar'],
                ]
            ],
        ];
    }

    public function testSaveDcaField()
    {
        require_once __DIR__.'/../Fixtures/Backend.php';

        $tagModel = new DummyModel();
        $tagModel->id = 456;

        $dummyModel = new DummyModel();
        $dummyModel->id = 123;
        $dummyModel->name = 'foobar';
        $dummyModel->source = 'foobar';

        $this->framework->method('createInstance')->willReturn($tagModel);

        $this->framework->method('getAdapter')->willReturnOnConsecutiveCalls(
            new DummyModel(['findByPk' => $dummyModel]),
            new ExtraDummyModel(['findByPk' => null])
        );

        $output = $this->manager->saveDcaField(serialize(['123', 'new']), $this->createMock(DataContainer::class));

        static::assertEquals(serialize(['123', '456']), $output);
    }

    public function testGetFilterOptions()
    {
        require_once __DIR__.'/../Fixtures/Backend.php';

        $tagModel = new DummyModel();
        $tagModel->id = 123;
        $tagModel->name = 'Foobar';

        $relationsModelAdapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelatedValues'])
            ->getMock();
        ;

        $relationsModelAdapter
            ->method('getRelatedValues')
            ->willReturn([1, 2, 3])
        ;

        $tagModelAdapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['findByCriteria'])
            ->getMock();
        ;

        $tagModelAdapter
            ->method('findByCriteria')
            ->willReturn(new Collection([$tagModel], ''))
        ;

        $this->framework
            ->method('getAdapter')
            ->willReturnMap([
                [Model::class, $relationsModelAdapter],
                [TagModel::class, $tagModelAdapter]
            ]);
        ;

        $dataContainer = $this->createMock(DataContainer::class);

        static::assertSame([123 => 'Foobar'], $this->manager->getFilterOptions($dataContainer));
    }

    public function testFindRelatedSourceRecords()
    {
        $relations = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelation'])
            ->getMock()
        ;

        $relations
            ->method('getRelation')
            ->willReturn([
                'reference_field' => 'foo_id',
                'related_field' => 'bar_id',
                'table' => 'tl_foobar',
            ])
        ;

        $model = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelatedValues'])
            ->getMock()
        ;

        $model
            ->method('getRelatedValues')
            ->willReturn([1, 2, 3, 4])
        ;

        $this->framework
            ->method('getAdapter')
            ->willReturnMap([
                [Relations::class, $relations],
                [Model::class, $model],
            ]);
        ;

        $this->db
            ->method('fetchAll')
            ->willReturn([
                ['foo_id' => 1, 'relevance' => 4],
                ['foo_id' => 2, 'relevance' => 2],
            ])
        ;

        static::assertSame(
            [
                1 => [
                    'total' => 4,
                    'found' => 4,
                    'prcnt' => 100,
                ],
                2 => [
                    'total' => 4,
                    'found' => 2,
                    'prcnt' => 50.0,
                ],
            ],
            $this->manager->findRelatedSourceRecords(1, 2)
        );
    }

    public function testFindRelatedSourceRecordsNoRelation()
    {
        $relations = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelation'])
            ->getMock()
        ;

        $relations
            ->method('getRelation')
            ->willReturn(false)
        ;

        $this->framework
            ->method('getAdapter')
            ->willReturn($relations);
        ;

        $this->expectException(\RuntimeException::class);
        $this->manager->findRelatedSourceRecords(1);
    }

    public function testFindRelatedSourceRecordsEmpty()
    {
        $relations = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelation'])
            ->getMock()
        ;

        $relations
            ->method('getRelation')
            ->willReturn([])
        ;

        $model = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelatedValues'])
            ->getMock()
        ;

        $model
            ->method('getRelatedValues')
            ->willReturn([])
        ;

        $this->framework
            ->method('getAdapter')
            ->willReturnMap([
                [Relations::class, $relations],
                [Model::class, $model],
            ]);
        ;

        static::assertEmpty($this->manager->findRelatedSourceRecords(1));
    }
}
