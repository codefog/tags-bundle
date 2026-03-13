<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Test\EventListener;

use Codefog\TagsBundle\EventListener\TagManagerListener;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Codefog\TagsBundle\Test\Fixtures\DummyManager;
use Contao\DataContainer;
use PHPUnit\Framework\TestCase;

final class TagManagerListenerTest extends TestCase
{
    public function testOnLoadDataContainer(): void
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

        $GLOBALS['TL_CONFIG']['debugMode'] = false;

        $this->mockListener()->onLoadDataContainer('tl_table');

        $this->assertSame(
            [
                'field1' => [
                    'inputType' => 'cfgTags',
                    'eval' => ['tagsManager' => 'foobar', 'tagsSource' => 'tl_table.field1'],
                    'dummy' => true,
                ],
                'field2' => [
                    'inputType' => 'text',
                ],
            ],
            $GLOBALS['TL_DCA']['tl_table']['fields'],
        );
    }

    public function testOnLoadDataContainerNoFields(): void
    {
        $GLOBALS['TL_DCA']['tl_table'] = [];

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->method('get')
            ->willReturn(new DummyManager())
        ;

        $listener = new TagManagerListener($registry);
        $listener->onLoadDataContainer('tl_table');

        $this->assertSame([], $GLOBALS['TL_DCA']['tl_table']);
    }

    public function testOnFieldSaveCallback(): void
    {
        $GLOBALS['TL_DCA']['tl_table']['fields'] = [
            'field' => [
                'eval' => ['tagsManager' => 'foobar'],
            ],
        ];

        $dataContainer = $this->createMock(DataContainer::class);
        $dataContainer
            ->expects($this->exactly(2))
            ->method('__get')
            ->willReturnMap([
                ['table', 'tl_table'],
                ['field', 'field'],
            ])
        ;

        $this->assertSame('FOOBAR', $this->mockListener()->onFieldSaveCallback('foobar', $dataContainer));
    }

    public function testOnFieldSaveCallbackManagerUnsupported(): void
    {
        $GLOBALS['TL_DCA']['tl_table']['fields'] = [
            'field' => [
                'eval' => ['tagsManager' => 'foobar'],
            ],
        ];

        $dataContainer = $this->createMock(DataContainer::class);
        $dataContainer
            ->expects($this->exactly(2))
            ->method('__get')
            ->willReturnMap([
                ['table', 'tl_table'],
                ['field', 'field'],
            ])
        ;

        $listener = $this->mockListener($this->createStub(ManagerInterface::class));

        $this->assertSame('foobar', $listener->onFieldSaveCallback('foobar', $dataContainer));
    }

    public function testOnOptionsCallback(): void
    {
        $GLOBALS['TL_DCA']['tl_table']['fields'] = [
            'field' => [
                'eval' => ['tagsManager' => 'foobar'],
            ],
        ];

        $dataContainer = $this->createMock(DataContainer::class);
        $dataContainer
            ->expects($this->exactly(2))
            ->method('__get')
            ->willReturnMap([
                ['table', 'tl_table'],
                ['field', 'field'],
            ])
        ;

        $this->assertSame(['foo', 'bar'], $this->mockListener()->onOptionsCallback($dataContainer));
    }

    public function testOnOptionsCallbackManagerUnsupported(): void
    {
        $GLOBALS['TL_DCA']['tl_table']['fields'] = [
            'field' => [
                'eval' => ['tagsManager' => 'foobar'],
            ],
        ];

        $dataContainer = $this->createMock(DataContainer::class);
        $dataContainer
            ->expects($this->exactly(2))
            ->method('__get')
            ->willReturnMap([
                ['table', 'tl_table'],
                ['field', 'field'],
            ])
        ;

        $listener = $this->mockListener($this->createStub(ManagerInterface::class));

        $this->assertSame([], $listener->onOptionsCallback($dataContainer));
    }

    private function mockListener($manager = null): TagManagerListener
    {
        $registry = $this->createConfiguredMock(ManagerRegistry::class, [
            'get' => $manager ?? new DummyManager(),
        ]);

        return new TagManagerListener($registry);
    }
}
