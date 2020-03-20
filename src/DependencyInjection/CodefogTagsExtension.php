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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class CodefogTagsExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('listener.yml');
        $loader->load('services.yml');

        foreach ($mergedConfig['managers'] as $name => $manager) {
            $this->createManager($name, $manager, $container);
        }
    }

    /**
     * Create the manager.
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
            $container->setAlias($config['alias'], $id)->setPublic(true);
        }
    }
}
