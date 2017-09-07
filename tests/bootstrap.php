<?php

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = include_once __DIR__ . '/../../../autoload.php';

$loader->addPsr4('Contao\\CoreBundle\\Tests\\', __DIR__ . '/../../../contao/core-bundle/tests');
$loader->register();

abstract class System extends \Contao\System {}
abstract class Config extends \Contao\Config {}