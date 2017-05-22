<?php


namespace Codefog\TagsBundle\Test\DependencyInjection\Compiler;

use Codefog\TagsBundle\DependencyInjection\Compiler\ManagerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ManagerPassTest extends TestCase
{
    /**
     * @var ManagerPass
     */
    private $managerPass;

    public function setUp()
    {
        $this->managerPass = new ManagerPass('registry', 'tag');
    }

    public function testInstantiation()
    {
        static::assertInstanceOf(ManagerPass::class, $this->managerPass);
    }

    public function testProcess()
    {
        $registryDefinition = new Definition();

        $managerDefinition1 = new Definition();
        $managerDefinition1->addTag('tag', ['alias' => 'foo']);

        $managerDefinition2 = new Definition();
        $managerDefinition2->addTag('tag', ['alias' => 'bar']);

        $container = new ContainerBuilder();
        $container->addDefinitions([
            'registry' => $registryDefinition,
            'manager1' => $managerDefinition1,
            'manager2' => $managerDefinition2,
        ]);

        $this->managerPass->process($container);
        
        $calls = $registryDefinition->getMethodCalls();
        
        static::assertEquals('add', $calls[0][0]);
        static::assertInstanceOf(Reference::class, $calls[0][1][0]);
        static::assertEquals('foo', $calls[0][1][1]);
        static::assertEquals('bar', $calls[1][1][1]);
    }

    public function testRegistryNotExists()
    {
        $this->expectException(\RuntimeException::class);
        $this->managerPass->process(new ContainerBuilder());
    }
}
