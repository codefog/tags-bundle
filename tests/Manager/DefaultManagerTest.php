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
use Haste\Model\Model;
use PHPUnit\Framework\TestCase;

class DefaultManagerTest extends TestCase
{
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

        $this->manager = new DefaultManager($this->framework, 'tl_table', 'field');
        $this->manager->setAlias('foobar');
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
}
