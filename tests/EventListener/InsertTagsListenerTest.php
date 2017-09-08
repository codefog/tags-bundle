<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Test\EventListener;

use Codefog\TagsBundle\EventListener\InsertTagsListener;
use Codefog\TagsBundle\Model\TagModel;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model\Collection;
use PHPUnit\Framework\TestCase;

class InsertTagsListenerTest extends TestCase
{
    /** @var  InsertTagsListener */
    protected $listener;

    protected function setUp()
    {
        $this->listener = new InsertTagsListener($this->mockContaoFramework());
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(InsertTagsListener::class, $this->listener);
    }

    public function testOnReplaceInsertTags()
    {
        $this->assertSame(
            'The "foobar" tag',
            $this->listener->onReplaceInsertTags('tags_title::2::1')
        );
    }

    public function testReturnsFalseIfTheTagIsUnknown()
    {
        $listener = new InsertTagsListener($this->mockContaoFramework());

        $this->assertFalse($listener->onReplaceInsertTags('link_url::2'));
    }

    private function mockContaoFramework($source = 'default', $noModels = false)
    {
        $tagModel = $this->createMock(Collection::class);

        $tagModel
            ->method('__get')
            ->willReturnCallback(function ($key) {
                switch ($key) {
                    case 'title':
                        return 'The "foobar" tag';
                    default:
                        return null;
                }
            });

        $tagModel->method('first')->willReturnCallback(function () {
            $instance = $this->createMock(TagModel::class);
            $instance->method('__get')->willReturnCallback(function ($key) {
                switch ($key) {
                    case 'name':
                        return 'The "foobar" tag';
                    default:
                        return null;
                }
            });
            return $instance;
        });

        $tagModelAdapter = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['findByCriteria'])
            ->getMock();

        $tagModelAdapter
            ->method('findByCriteria')
            ->willReturn($noModels ? null : $tagModel);

        $framework = $this->createMock(ContaoFrameworkInterface::class);

        $framework
            ->method('isInitialized')
            ->willReturn(true);

        $framework
            ->method('getAdapter')
            ->willReturnCallback(function ($key) use ($tagModelAdapter) {
                switch ($key) {
                    case TagModel::class:
                        return $tagModelAdapter;
                    default:
                        return null;
                }
            });

        return $framework;
    }
}
