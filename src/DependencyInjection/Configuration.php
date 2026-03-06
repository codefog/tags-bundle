<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('codefog_tags');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('managers')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                    ->children()
                        ->variableNode('source')
                            ->isRequired()
                            ->validate()
                                ->ifString()
                                ->then(
                                    static fn (string $value): array => [$value],
                                )
                            ->end()
                        ->end()
                        ->scalarNode('service')->defaultValue('codefog_tags.default_manager')->end()
                        ->scalarNode('alias')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
