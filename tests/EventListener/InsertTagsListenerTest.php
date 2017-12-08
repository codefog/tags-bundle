<?php

namespace Codefog\TagsBundle\Test\EventListener;

use Codefog\TagsBundle\EventListener\InsertTagsListener;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Codefog\TagsBundle\Tag;
use PHPUnit\Framework\TestCase;

class InsertTagsListenerTest extends TestCase
{
    public function testInstantiation()
    {
        static::assertInstanceOf(InsertTagsListener::class, new InsertTagsListener($this->createMock(ManagerRegistry::class)));
    }

    /**
     * @dataProvider insertTagsDataProvider
     */
    public function testOnReplaceInsertTagsNotSupported($insertTag, $tag, $expected)
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager
            ->method('find')
            ->willReturn($tag)
        ;

        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->method('get')
            ->willReturn($manager)
        ;

        $listener = new InsertTagsListener($registry);

        static::assertEquals($expected, $listener->onReplaceInsertTags($insertTag));
    }

    public function insertTagsDataProvider()
    {
        $tag = new Tag(123, 'Foobar');
        $tag->setData(['foo' => 'Foo']);

        return [
            'Unsupported tag' => ['foobar', null, false],
            'Invalid tag' => ['tag::foobar', null, false],
            'Tag not found' => ['tag::foobar::123::name', null, ''],
            'Tag name' => ['tag::foobar::123::name', $tag, 'Foobar'],
            'Tag property' => ['tag::foobar::123::foo', $tag, 'Foo'],
            'Tag property not found' => ['tag::foobar::123::bar', $tag, ''],
        ];
    }
}
