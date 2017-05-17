<?php

namespace Codefog\TagsBundle\Test\EventListener;

use Codefog\TagsBundle\EventListener\TagManagerListener;
use Codefog\TagsBundle\ManagerRegistry;
use Codefog\TagsBundle\Test\Fixtures\DummyManager;
use Contao\DataContainer;
use PHPUnit\Framework\TestCase;

class TagManagerListenerTest extends TestCase
{
    public function testInstantiation()
    {
        static::assertInstanceOf(TagManagerListener::class, new TagManagerListener($this->createMock(ManagerRegistry::class)));
    }

    public function testOnLoadDataContainer()
    {
        $GLOBALS['TL_DCA']['tl_table']['fields'] = [
            'field1' => [
                'inputType' => 'cfgTags',
                'eval' => ['tagsManager' => 'foobar'],
            ],
            'field2' => [
                'inputType' => 'text',
            ],
        ];

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('get')->willReturn(new DummyManager());

        $listener = new TagManagerListener($registry);
        $listener->onLoadDataContainer('tl_table');

        static::assertEquals([
            'field1' => [
                'inputType' => 'cfgTags',
                'eval' => ['tagsManager' => 'foobar'],
                'dummy' => true,
            ],
            'field2' => [
                'inputType' => 'text',
            ],
        ], $GLOBALS['TL_DCA']['tl_table']['fields']);
    }

    public function testOnLoadDataContainerNoFields()
    {
        $GLOBALS['TL_DCA']['tl_table'] = [];

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('get')->willReturn(new DummyManager());

        $listener = new TagManagerListener($registry);
        $listener->onLoadDataContainer('tl_table');

        static::assertEquals([], $GLOBALS['TL_DCA']['tl_table']);
    }

    public function testOnFieldSave()
    {
        require_once __DIR__.'/../Fixtures/Backend.php';
        require_once __DIR__.'/../Fixtures/Controller.php';

        $GLOBALS['TL_DCA']['tl_table']['fields'] = [
            'field' => [
                'eval' => ['tagsManager' => 'foobar']
            ],
        ];

        $dataContainer = $this->createMock(DataContainer::class);
        $dataContainer->method('__get')->willReturnOnConsecutiveCalls('tl_table', 'field');

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('get')->willReturn(new DummyManager());

        $listener = new TagManagerListener($registry);

        static::assertEquals('FOOBAR', $listener->onFieldSave('foobar', $dataContainer));
    }
}
