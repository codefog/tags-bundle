<?php

declare(strict_types=1);

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class CodefogTagsExtension extends ConfigurableExtension
{
    /**
     * @inheritDoc
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('listener.yml');
        $loader->load('services.yml');

        foreach ($mergedConfig['managers'] as $name => $manager) {
            $this->createManager($name, $manager, $container);
        }
    }

    /**
     * Create the manager
     *
     * @param string $name
     * @param array $config
     * @param ContainerBuilder $container
     */
    private function createManager(string $name, array $config, ContainerBuilder $container): void
    {
        $id = sprintf('codefog_tags.manager.%s', $name);

        $container
            ->setDefinition($id, new ChildDefinition($config['service']))
            ->setArguments([$name, $config['table'], $config['field']])
            ->addTag('codefog_tags.default_manager', ['name' => $name])
            ->setPublic(true)
        ;

        // Create an alias
        if (isset($config['alias'])) {
            $container->getDefinition($id)->setPublic(false);

            if (null === ($alias = $container->setAlias($config['alias'], $id))) {
                $alias = $container->getAlias($config['alias']);
            }

            $alias->setPublic(true);
        }
    }
}
