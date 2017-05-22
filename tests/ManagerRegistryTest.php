<?php

namespace Codefog\TagsBundle\Test;

use Codefog\TagsBundle\Manager\ManagerInterface;
use Codefog\TagsBundle\ManagerRegistry;
use PHPUnit\Framework\TestCase;

class ManagerRegistryTest extends TestCase
{
    public function testInstantiation()
    {
        static::assertInstanceOf(ManagerRegistry::class, new ManagerRegistry());
    }

    public function testAddManager()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $registry = new ManagerRegistry();
        $registry->add($managerMock, 'foobar');

        static::assertEquals($managerMock, $registry->get('foobar'));
    }

    public function testManagerNotExists()
    {
        $this->expectException(\InvalidArgumentException::class);
        $registry = new ManagerRegistry();
        $registry->get('foobar');
    }

    public function testGetAliases()
    {
        $managerMock = $this->createMock(ManagerInterface::class);

        $registry = new ManagerRegistry();
        $registry->add($managerMock, 'foobar');
        $registry->add($managerMock, 'foobaz');

        static::assertEquals(['foobar', 'foobaz'], $registry->getAliases());
    }
}
