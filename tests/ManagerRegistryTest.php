<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Test;

use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class ManagerRegistryTest extends TestCase
{
    public function testAddManager(): void
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $registry = $this->mockRegistry();
        $registry->add($managerMock, 'foobar');

        $this->assertSame($managerMock, $registry->get('foobar'));
    }

    public function testManagerNotExists(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $registry = $this->mockRegistry();
        $registry->get('foobar');
    }

    public function testGetAliases(): void
    {
        $manager1Mock = $this->createMock(ManagerInterface::class);
        $manager2Mock = $this->createMock(DefaultManager::class);

        $registry = $this->mockRegistry();
        $registry->add($manager1Mock, 'foobar');
        $registry->add($manager2Mock, 'foobaz');

        $managers = $registry->all();

        $this->assertArrayHasKey('foobar', $managers);
        $this->assertArrayHasKey('foobaz', $managers);
        $this->assertInstanceOf(ManagerInterface::class, $managers['foobar']);
        $this->assertInstanceOf(DefaultManager::class, $managers['foobaz']);
    }

    private function mockRegistry(): ManagerRegistry
    {
        return new ManagerRegistry();
    }
}
