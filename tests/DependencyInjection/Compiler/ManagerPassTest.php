<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Test\DependencyInjection\Compiler;

use Codefog\TagsBundle\DependencyInjection\Compiler\ManagerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ManagerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $registryDefinition = new Definition();

        $managerDefinition1 = new Definition();
        $managerDefinition1->addTag(ManagerPass::TAG_NAME, ['alias' => 'foo']);

        $managerDefinition2 = new Definition();
        $managerDefinition2->addTag(ManagerPass::TAG_NAME, ['alias' => 'bar']);

        $container = new ContainerBuilder();
        $container->addDefinitions([
            'registry' => $registryDefinition,
            'manager1' => $managerDefinition1,
            'manager2' => $managerDefinition2,
        ]);

        $this->mockCompilerPass()->process($container);

        $calls = $registryDefinition->getMethodCalls();

        $this->assertSame('add', $calls[0][0]);
        $this->assertInstanceOf(Reference::class, $calls[0][1][0]);
        $this->assertSame('foo', $calls[0][1][1]);
        $this->assertSame('bar', $calls[1][1][1]);
    }

    public function testRegistryNotExists(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->mockCompilerPass()->process(new ContainerBuilder());
    }

    private function mockCompilerPass(): ManagerPass
    {
        return new ManagerPass('registry');
    }
}
