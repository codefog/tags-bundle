<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Test\Manager;

use Codefog\TagsBundle\Collection\CollectionInterface;
use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Tag;
use Codefog\TagsBundle\Test\Fixtures\DummyModel;
use Codefog\TagsBundle\Test\Fixtures\ExtraDummyModel;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
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

    public function testUpdateDcaField()
    {
        $this->framework->method('getAdapter')->willReturn(
            new DummyModel(['getTable' => 'tl_cfg_tag'])
        );

        // Variant with save_callback not exists
        $config = [];
        $this->manager->updateDcaField($config);

        static::assertEquals(
            [
                'relation' => [
                    'type' => 'haste-ManyToMany',
                    'load' => 'lazy',
                    'table' => 'tl_cfg_tag',
                ],
                'save_callback' => [
                    ['codefog_tags.listener.tag_manager', 'onFieldSave'],
                ],
            ],
            $config
        );

        // Variant with save_callback exists
        $config = [
            'save_callback' => [
                ['foo', 'bar'],
            ],
        ];

        $this->manager->updateDcaField($config);

        static::assertEquals(
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
            ],
            $config
        );
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
}
