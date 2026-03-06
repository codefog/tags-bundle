<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Test;

use Codefog\TagsBundle\CodefogTagsBundle;
use Codefog\TagsBundle\DependencyInjection\Compiler\ManagerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CodefogTagsBundleTest extends TestCase
{
    public function testInstantiation(): void
    {
        $this->assertInstanceOf(CodefogTagsBundle::class, new CodefogTagsBundle());
    }

    public function testRegisterCompilerPass(): void
    {
        $container = new ContainerBuilder();
        $bundle = new CodefogTagsBundle();
        $bundle->build($container);
        $found = false;

        foreach ($container->getCompiler()->getPassConfig()->getPasses() as $pass) {
            if ($pass instanceof ManagerPass) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }
}
