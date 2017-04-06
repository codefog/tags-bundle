<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ManagerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $registryName;

    /**
     * @var string
     */
    private $tagName;

    /**
     * ManagerPass constructor.
     *
     * @param string $registryName
     * @param string $tagName
     */
    public function __construct($registryName, $tagName)
    {
        $this->registryName = $registryName;
        $this->tagName      = $tagName;
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->registryName)) {
            return;
        }

        $definition = $container->getDefinition($this->registryName);

        foreach ($container->findTaggedServiceIds($this->tagName) as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('add', [new Reference($id), $attributes['alias']]);
            }
        }
    }
}
