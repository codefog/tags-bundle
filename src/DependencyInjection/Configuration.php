<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('codefog_tags');
        $treeBuilder->getRootNode()
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
                                    static function (string $value): array {
                                        return [$value];
                                    }
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
