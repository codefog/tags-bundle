<?php

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Expression\DecorateWillReturnMapWithExpectsMockRector;

return RectorConfig::configure()
    ->withSkip([DecorateWillReturnMapWithExpectsMockRector::class]);
