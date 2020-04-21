<?php

namespace Codefog\TagsBundle\Test\ContaoManager;

use Codefog\TagsBundle\CodefogTagsBundle;
use Codefog\TagsBundle\ContaoManager\Plugin;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    public function testGetBundles()
    {
        $plugin = new Plugin();
        $bundles = $plugin->getBundles($this->createMock(ParserInterface::class));

        /** @var BundleConfig $config */
        $config = $bundles[0];

        $this->assertCount(1, $bundles);
        $this->assertInstanceOf(BundleConfig::class, $config);
        $this->assertEquals(CodefogTagsBundle::class, $config->getName());
        $this->assertEquals([ContaoCoreBundle::class, 'haste'], $config->getLoadAfter());
    }
}
