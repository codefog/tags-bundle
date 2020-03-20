<?php


namespace Codefog\TagsBundle\Test\DependencyInjection\Compiler;

use Codefog\TagsBundle\DependencyInjection\Compiler\ManagerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ManagerPassTest extends TestCase
{
    public function testProcess()
    {
        $registryDefinition = new Definition();

        $managerDefinition1 = new Definition();
        $managerDefinition1->addTag('tag', ['name' => 'foo']);

        $managerDefinition2 = new Definition();
        $managerDefinition2->addTag('tag', ['name' => 'bar']);

        $container = new ContainerBuilder();
        $container->addDefinitions([
            'registry' => $registryDefinition,
            'manager1' => $managerDefinition1,
            'manager2' => $managerDefinition2,
        ]);

        $this->mockCompilerPass()->process($container);

        $calls = $registryDefinition->getMethodCalls();

        $this->assertEquals('add', $calls[0][0]);
        $this->assertInstanceOf(Reference::class, $calls[0][1][0]);
        $this->assertEquals('foo', $calls[0][1][1]);
        $this->assertEquals('bar', $calls[1][1][1]);
    }

    public function testRegistryNotExists()
    {
        $this->expectException(\RuntimeException::class);
        $this->mockCompilerPass()->process(new ContainerBuilder());
    }

    private function mockCompilerPass(): ManagerPass
    {
        return new ManagerPass('registry', 'tag');
    }
}
