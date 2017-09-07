<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Test\EventListener\DataContainer;

use Codefog\TagsBundle\EventListener\DataContainer\TagListener;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Codefog\TagsBundle\Tag;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Model;
use PHPUnit\Framework\TestCase;

class TagListenerTest extends TestCase
{
    /**
     * @var TagListener
     */
    private $listener;

    public function setUp()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->method('find')->willReturn(new Tag('', ''));
        $manager->method('countSourceRecords')->willReturn(0);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('get')->willReturn($manager);
        $registry->method('getAliases')->willReturn(['foo', 'bar']);

        $this->listener = new TagListener($registry);
    }

    public function testInstantiation()
    {
        static::assertInstanceOf(TagListener::class, $this->listener);
    }

    public function testGenerateLabel()
    {
        require_once __DIR__ . '/../../Fixtures/Backend.php';

        static::assertNotEmpty(
            $this->listener->generateLabel(
                ['source' => '', 'id' => ''],
                '',
                $this->createMock(DataContainer::class),
                ['foo' => 'bar']
            )
        );
    }

    public function testGetSources()
    {
        static::assertEquals(['foo', 'bar'], $this->listener->getSources());
    }

    public function testGenerateAlias()
    {
        $dc = $this->createMock(DataContainer::class);
        $dc->method('__get')->willReturnCallback(function ($key) {
            switch ($key) {
                case 'activeRecord':
                    $activeRecord = $this->createMock(Model::class);
                    $activeRecord->method('__get')->willReturnCallback(function ($key) {
                        switch ($key) {
                            case 'name':
                                return 'My example Alias';
                            default:
                                return null;
                        }
                    });

                    return $activeRecord;
                default:
                    return null;
            }
        });

        $existingAliases = $this->createMock(Result::class);

        $existingAliases->method('__get')->willReturnCallback(function ($key) {
            switch ($key) {
                case 'numRows':
                    return 0;
                default:
                    return null;
            }
        });

        // no existing alias
        $this->listener->setExistingAliases($existingAliases);

        static::assertEquals(
            'my-example-alias',
            $this->listener->generateAlias('', $dc)
        );

        // alias already existing
        $existingAliases->method('__get')->willReturnCallback(function ($key) {
            switch ($key) {
                case 'numRows':
                    return 1;
                default:
                    return null;
            }
        });

        $this->listener->setExistingAliases($existingAliases);

        static::assertEquals(
            'my-example-alias',
            $this->listener->generateAlias('', $dc)
        );
    }

    public function testAddAliasButton()
    {
        $dc = $this->createMock(DataContainer::class);

        static::assertEquals(
            ['alias' => '<button type="submit" name="alias" id="alias" class="tl_submit" accesskey="a"></button>'],
            $this->listener->addAliasButton([], $dc)
        );
    }
}
