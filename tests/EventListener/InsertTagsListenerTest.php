<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Test\EventListener;

use Codefog\TagsBundle\EventListener\InsertTagsListener;
use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class InsertTagsListenerTest extends TestCase
{
    public function testOnReplaceInsertTagsNotSupported(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $listener = $this->mockListener($manager);

        $this->assertSame('', $listener->onReplaceInsertTags('tag::foo::bar::name'));
    }

    /**
     * @dataProvider insertTagsDataProvider
     */
    public function testOnReplaceInsertTags($insertTag, $value, $expected): void
    {
        $manager = $this->createConfiguredMock(DefaultManager::class, ['getInsertTagValue' => $value]);
        $listener = $this->mockListener($manager);

        $this->assertSame($expected, $listener->onReplaceInsertTags($insertTag));
    }

    public static function insertTagsDataProvider(): iterable
    {
        return [
            'Unsupported tag' => ['foobar', '', false],
            'Invalid tag' => ['tag::foobar', '', false],
            'Tag not found' => ['tag::foobar::123::name', '', ''],
            'Tag name' => ['tag::foobar::123::name', 'Foobar', 'Foobar'],
            'Tag property' => ['tag::foobar::123::foo', '123', '123'],
            'Tag property not found' => ['tag::foobar::123::bar', 'bar', 'bar'],
        ];
    }

    private function mockListener($manager): InsertTagsListener
    {
        $registry = $this->createConfiguredMock(ManagerRegistry::class, [
            'get' => $manager,
        ]);

        return new InsertTagsListener($registry);
    }
}
