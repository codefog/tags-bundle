<?php


namespace Codefog\TagsBundle\Test\DependencyInjection;

use Codefog\TagsBundle\DependencyInjection\CodefogTagsExtension;
use Codefog\TagsBundle\DependencyInjection\Compiler\ManagerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CodefogTagsExtensionTest extends TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder();
        $extension = new CodefogTagsExtension();

        $extension->load([
            'codefog_tags' => [
                'managers' => [
                    'foo_manager' => [
                        'table' => 'tl_table_foo',
                        'field' => 'tags_foo',
                        'service' => ManagerPass::TAG_NAME,
                    ],
                    'bar_manager' => [
                        'table' => 'tl_table_bar',
                        'field' => 'tags_bar',
                        'service' => 'codefog_tags.custom_manager',
                        'alias' => 'bar_alias'
                    ],
                ],
            ],
        ], $container);

        // Listeners
        $this->assertTrue($container->hasDefinition('codefog_tags.listener.insert_tags'));
        $this->assertTrue($container->hasDefinition('codefog_tags.listener.tag_manager'));
        $this->assertTrue($container->hasDefinition('codefog_tags.listener.data_container.tag'));

        // Services
        $this->assertTrue($container->hasDefinition('codefog_tags.default_manager'));
        $this->assertTrue($container->hasDefinition('codefog_tags.tag_finder'));
        $this->assertTrue($container->hasDefinition('codefog_tags.source_finder'));
        $this->assertTrue($container->hasDefinition('codefog_tags.manager_registry'));
        $this->assertTrue($container->getDefinition('codefog_tags.manager_registry')->isPublic());

        // Manager services – foo_manager
        $definition = $container->getDefinition('codefog_tags.manager.foo_manager');

        $this->assertTrue($container->hasDefinition('codefog_tags.manager.foo_manager'));
        $this->assertEquals('foo_manager', $definition->getArgument(0));
        $this->assertEquals('tl_table_foo', $definition->getArgument(1));
        $this->assertEquals('tags_foo', $definition->getArgument(2));
        $this->assertEquals([['alias' => 'foo_manager']], $definition->getTag(ManagerPass::TAG_NAME));
        $this->assertTrue($definition->isPublic());

        // Manager services – bar_manager
        $definition = $container->getDefinition('codefog_tags.manager.bar_manager');

        $this->assertTrue($container->hasDefinition('codefog_tags.manager.bar_manager'));
        $this->assertEquals('bar_manager', $definition->getArgument(0));
        $this->assertEquals('tl_table_bar', $definition->getArgument(1));
        $this->assertEquals('tags_bar', $definition->getArgument(2));
        $this->assertEquals([['alias' => 'bar_manager']], $definition->getTag(ManagerPass::TAG_NAME));
        $this->assertTrue($definition->isPublic());
        $this->assertTrue($container->hasAlias('bar_alias'));
    }
}
