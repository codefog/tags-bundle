<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\TagsBundle\Test;

use Codefog\TagsBundle\CodefogTagsBundle;
use Codefog\TagsBundle\DependencyInjection\Compiler\ManagerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CodefogTagsBundleTest extends TestCase
{
    public function testInstantiation()
    {
        static::assertInstanceOf(CodefogTagsBundle::class, new CodefogTagsBundle());
    }

    public function testRegisterCompilerPass()
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

        static::assertTrue($found);
    }
}
