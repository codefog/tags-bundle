<?php

namespace Codefog\TagsBundle\Test;

use Codefog\TagsBundle\Manager\DefaultManager;
use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

class ManagerRegistryTest extends TestCase
{
    public function testInstantiation()
    {
        static::assertInstanceOf(ManagerRegistry::class, new ManagerRegistry($this->createMock(Connection::class)));
    }

    public function testAddManager()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $registry = new ManagerRegistry($this->createMock(Connection::class));
        $registry->add($managerMock, 'foobar');

        static::assertEquals($managerMock, $registry->get('foobar'));
    }

    public function testManagerNotExists()
    {
        $this->expectException(\InvalidArgumentException::class);
        $registry = new ManagerRegistry($this->createMock(Connection::class));
        $registry->get('foobar');
    }

    public function testGetAliases()
    {
        $manager1Mock = $this->createMock(ManagerInterface::class);
        $manager2Mock = $this->createMock(DefaultManager::class);

        $registry = new ManagerRegistry($this->createMock(Connection::class));
        $registry->add($manager1Mock, 'foobar');
        $registry->add($manager2Mock, 'foobaz');

        static::assertEquals(['foobar', 'foobaz'], $registry->getNames());
    }
}
