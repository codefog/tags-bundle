<?php


namespace Codefog\TagsBundle\Test\DependencyInjection;

use Codefog\TagsBundle\DependencyInjection\CodefogTagsExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CodefogTagsExtensionTest extends TestCase
{
    public function testLoad()
    {
        $container = new ContainerBuilder();
        $extension = new CodefogTagsExtension();
        $extension->load([], $container);

        // Listeners
        $this->assertTrue($container->hasDefinition('codefog_tags.listener.tag_manager'));
        $this->assertTrue($container->hasDefinition('codefog_tags.listener.data_container.tag'));

        // Services
        $this->assertTrue($container->hasDefinition('codefog_tags.manager_registry'));
    }
}
