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

        define('TL_MODE', 'BE');
        $GLOBALS['TL_CSS'] = [];
        $GLOBALS['TL_JAVASCRIPT'] = [];

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

        static::assertContains('bundles/codefogtags/selectize.min.css', $GLOBALS['TL_CSS']);
        static::assertContains('bundles/codefogtags/backend.min.css', $GLOBALS['TL_CSS']);
        static::assertContains('assets/jquery/js/jquery.min.js', $GLOBALS['TL_JAVASCRIPT']);
        static::assertContains('bundles/codefogtags/selectize.min.js', $GLOBALS['TL_JAVASCRIPT']);
        static::assertContains('bundles/codefogtags/widget.min.js', $GLOBALS['TL_JAVASCRIPT']);
        static::assertContains('bundles/codefogtags/backend.min.js', $GLOBALS['TL_JAVASCRIPT']);
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
        $dataContainer
            ->method('__get')
            ->willReturnMap([
                ['table', 'tl_table'],
                ['field', 'field'],
            ])
        ;

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('get')->willReturn(new DummyManager());

        $listener = new TagManagerListener($registry);

        static::assertEquals('FOOBAR', $listener->onFieldSave('foobar', $dataContainer));
    }

    public function testOnOptionsCallback()
    {
        require_once __DIR__.'/../Fixtures/Backend.php';
        require_once __DIR__.'/../Fixtures/Controller.php';

        $GLOBALS['TL_DCA']['tl_table']['fields'] = [
            'field' => [
                'eval' => ['tagsManager' => 'foobar']
            ],
        ];

        $dataContainer = $this->createMock(DataContainer::class);
        $dataContainer
            ->method('__get')
            ->willReturnMap([
                ['table', 'tl_table'],
                ['field', 'field'],
            ])
        ;

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('get')->willReturn(new DummyManager());

        $listener = new TagManagerListener($registry);

        static::assertEquals(['foo', 'bar'], $listener->onOptionsCallback($dataContainer));
    }
}
