<?php

declare(strict_types=1);

use Codefog\TagsBundle\Model\TagModel;
use Codefog\TagsBundle\Widget\TagsWidget;

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

$GLOBALS['BE_MOD']['content']['cfg_tags'] = [
    'tables' => ['tl_cfg_tag'],
];

// Backend widgets
$GLOBALS['BE_FFL']['cfgTags'] = TagsWidget::class;

// Models
$GLOBALS['TL_MODELS']['tl_cfg_tag'] = TagModel::class;
