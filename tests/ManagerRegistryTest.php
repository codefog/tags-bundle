<?php

namespace Codefog\TagsBundle\Test;

use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class ManagerRegistryTest extends TestCase
{
    public function testAddManager()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $registry = $this->mockRegistry();
        $registry->add($managerMock, 'foobar');

        $this->assertEquals($managerMock, $registry->get('foobar'));
    }

    public function testManagerNotExists()
    {
        $this->expectException(\InvalidArgumentException::class);

        $registry = $this->mockRegistry();
        $registry->get('foobar');
    }

    public function testGetAliases()
    {
        $manager1Mock = $this->createMock(ManagerInterface::class);
        $manager2Mock = $this->createMock(DefaultManager::class);

        $registry = $this->mockRegistry();
        $registry->add($manager1Mock, 'foobar');
        $registry->add($manager2Mock, 'foobaz');

        $this->assertEquals(['foobar', 'foobaz'], $registry->getNames());
    }

    private function mockRegistry(): ManagerRegistry
    {
        return new ManagerRegistry($this->createMock(Connection::class));
    }
}
