<?php

declare(strict_types = 1);

namespace Codefog\TagsBundle;

use Codefog\TagsBundle\DependencyInjection\Compiler\ManagerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CodefogTagsBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ManagerPass('cfg_tags.manager_registry', 'cfg_tags.manager'));
    }
}
