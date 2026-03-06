<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\ContaoManager;

use Codefog\HasteBundle\CodefogHasteBundle;
use Codefog\TagsBundle\CodefogTagsBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(CodefogTagsBundle::class)->setLoadAfter([ContaoCoreBundle::class, CodefogHasteBundle::class]),
        ];
    }
}
