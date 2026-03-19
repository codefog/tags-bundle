<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

readonly class ManagerPass implements CompilerPassInterface
{
    public const string TAG_NAME = 'codefog_tags.manager';

    public function __construct(private string $registryName)
    {
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->registryName)) {
            throw new \RuntimeException(\sprintf('The registry service "%s" does not exist', $this->registryName));
        }

        $definition = $container->getDefinition($this->registryName);

        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('add', [new Reference($id), $attributes['alias']]);
            }
        }
    }
}
