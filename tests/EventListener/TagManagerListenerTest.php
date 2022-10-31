<?php

namespace Codefog\TagsBundle\Test\EventListener;

use Codefog\TagsBundle\EventListener\TagManagerListener;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Codefog\TagsBundle\Test\Fixtures\DummyManager;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\DataContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TagManagerListenerTest extends TestCase
{
    public function testOnLoadDataContainer()
    {
        $GLOBALS['TL_DCA']['tl_table']['fields'] = [
            'field1' => [
                'inputType' => 'cfgTags',
                'eval' => ['tagsManager' => 'foobar', 'tagsSource' => 'tl_table.field1'],
            ],
            'field2' => [
                'inputType' => 'text',
            ],
        ];

        $GLOBALS['TL_CSS'] = [];
        $GLOBALS['TL_JAVASCRIPT'] = [];
        $GLOBALS['TL_CONFIG']['debugMode'] = false;

        $this->mockListener()->onLoadDataContainer('tl_table');

        $this->assertEquals([
            'field1' => [
                'inputType' => 'cfgTags',
                'eval' => ['tagsManager' => 'foobar', 'tagsSource' => 'tl_table.field1'],
                'dummy' => true,
            ],
            'field2' => [
                'inputType' => 'text',
            ],
        ], $GLOBALS['TL_DCA']['tl_table']['fields']);

        $this->assertContains('bundles/codefogtags/selectize.min.css', $GLOBALS['TL_CSS']);
        $this->assertContains('bundles/codefogtags/backend.min.css', $GLOBALS['TL_CSS']);
        $this->assertContains('assets/jquery/js/jquery.min.js', $GLOBALS['TL_JAVASCRIPT']);
        $this->assertContains('bundles/codefogtags/selectize.min.js', $GLOBALS['TL_JAVASCRIPT']);
        $this->assertContains('bundles/codefogtags/widget.min.js', $GLOBALS['TL_JAVASCRIPT']);
        $this->assertContains('bundles/codefogtags/backend.min.js', $GLOBALS['TL_JAVASCRIPT']);
    }

    public function testOnLoadDataContainerNoFields()
    {
        $GLOBALS['TL_DCA']['tl_table'] = [];

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('get')->willReturn(new DummyManager());

        $requestStack = $this->createConfiguredMock(RequestStack::class, [
            'getCurrentRequest' => new Request(),
        ]);

        $scopeMatcher = $this->createConfiguredMock(ScopeMatcher::class, [
            'isBackendRequest' => true,
        ]);

        $listener = new TagManagerListener($registry, $requestStack, $scopeMatcher);
        $listener->onLoadDataContainer('tl_table');

        $this->assertEquals([], $GLOBALS['TL_DCA']['tl_table']);
    }

    public function testOnFieldSaveCallback()
    {
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

        $this->assertEquals('FOOBAR', $this->mockListener()->onFieldSaveCallback('foobar', $dataContainer));
    }

    public function testOnFieldSaveCallbackManagerUnsupported()
    {
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

        $listener = $this->mockListener($this->createMock(ManagerInterface::class));

        $this->assertEquals('foobar', $listener->onFieldSaveCallback('foobar', $dataContainer));
    }

    public function testOnOptionsCallback()
    {
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

        $this->assertEquals(['foo', 'bar'], $this->mockListener()->onOptionsCallback($dataContainer));
    }

    public function testOnOptionsCallbackManagerUnsupported()
    {
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

        $listener = $this->mockListener($this->createMock(ManagerInterface::class));

        $this->assertEquals([], $listener->onOptionsCallback($dataContainer));
    }

    private function mockListener($manager = null): TagManagerListener
    {
        $registry = $this->createConfiguredMock(ManagerRegistry::class, [
            'get' => $manager ?? new DummyManager(),
        ]);

        $requestStack = $this->createConfiguredMock(RequestStack::class, [
            'getCurrentRequest' => new Request(),
        ]);

        $scopeMatcher = $this->createConfiguredMock(ScopeMatcher::class, [
            'isBackendRequest' => true,
        ]);

        return new TagManagerListener($registry, $requestStack, $scopeMatcher);
    }
}
