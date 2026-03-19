<?php

declare(strict_types=1);

namespace Codefog\TagsBundle\Test\EventListener;

use Codefog\TagsBundle\EventListener\AddAssetsListener;
use Contao\CoreBundle\Routing\ScopeMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class AddAssetsListenerTest extends TestCase
{
    public function testInvoke(): void
    {
        $GLOBALS['TL_CSS'] = [];
        $GLOBALS['TL_JAVASCRIPT'] = [];

        $this->mockListener()();

        $this->assertContains('bundles/codefog_tags/tags-widget.css', $GLOBALS['TL_CSS']);
        $this->assertContains('bundles/codefog_tags/tags-widget.js', $GLOBALS['TL_JAVASCRIPT']);
    }

    private function mockListener(): AddAssetsListener
    {
        $packages = $this->createMock(Packages::class);
        $packages
            ->method('getUrl')
            ->willReturnCallback(static fn (string $path, string $packageName): string => \sprintf('bundles/%s/%s', $packageName, $path))
        ;

        $requestStack = $this->createConfiguredMock(RequestStack::class, [
            'getCurrentRequest' => new Request(),
        ]);

        $scopeMatcher = $this->createConfiguredMock(ScopeMatcher::class, [
            'isBackendRequest' => true,
        ]);

        return new AddAssetsListener($packages, $requestStack, $scopeMatcher);
    }
}
