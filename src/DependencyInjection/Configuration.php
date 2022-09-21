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

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('codefog_tags');
        }

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
                                    static function (string $value): array {
                                        return [$value];
                                    }
                                )
                            ->end()
                        ->end()
                        ->scalarNode('service')->defaultValue('codefog_tags.default_manager')->end()
                        ->scalarNode('locale')->defaultValue('en')->end()
                        ->scalarNode('validChars')->defaultValue('0-9a-z')->end()
                        ->scalarNode('alias')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
