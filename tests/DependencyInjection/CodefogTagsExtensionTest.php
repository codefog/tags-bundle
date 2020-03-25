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
                    'manager_1' => [
                        'source' => 'tl_table_foo.tags_foo',
                        'service' => ManagerPass::TAG_NAME,
                    ],
                    'manager_2' => [
                        'source' => [
                            'tl_table_bar_1.tags_bar_1',
                            'tl_table_bar_2.tags_bar_2',
                        ],
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

        // Manager services – manager_1
        $definition = $container->getDefinition('codefog_tags.manager.manager_1');

        $this->assertTrue($container->hasDefinition('codefog_tags.manager.manager_1'));
        $this->assertEquals('manager_1', $definition->getArgument(0));
        $this->assertEquals(['tl_table_foo.tags_foo'], $definition->getArgument(1));
        $this->assertEquals([['alias' => 'manager_1']], $definition->getTag(ManagerPass::TAG_NAME));
        $this->assertTrue($definition->isPublic());

        // Manager services – manager_2
        $definition = $container->getDefinition('codefog_tags.manager.manager_2');

        $this->assertTrue($container->hasDefinition('codefog_tags.manager.manager_2'));
        $this->assertEquals('manager_2', $definition->getArgument(0));
        $this->assertEquals(['tl_table_bar_1.tags_bar_1', 'tl_table_bar_2.tags_bar_2'], $definition->getArgument(1));
        $this->assertEquals([['alias' => 'manager_2']], $definition->getTag(ManagerPass::TAG_NAME));
        $this->assertTrue($definition->isPublic());
        $this->assertTrue($container->hasAlias('bar_alias'));
    }
}
