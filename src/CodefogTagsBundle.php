<?php

declare(strict_types=1);

namespace Codefog\TagsBundle;

use Codefog\TagsBundle\DependencyInjection\Compiler\ManagerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CodefogTagsBundle extends Bundle
{
    #[\Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ManagerPass('codefog_tags.manager_registry'));
    }
}
