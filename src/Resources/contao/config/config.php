<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

$GLOBALS['BE_MOD']['content']['cfg_tags'] = [
    'tables' => ['tl_cfg_tag'],
];

/*
 * Backend widgets
 */
$GLOBALS['BE_FFL']['cfgTags'] = Codefog\TagsBundle\Widget\TagsWidget::class;

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cfg_tag'] = \Codefog\TagsBundle\Model\TagModel::class;

/*
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = ['codefog_tags.listener.insert_tags', 'onReplaceInsertTags'];

if (is_array($GLOBALS['TL_HOOKS']['loadDataContainer'])) {
    array_unshift($GLOBALS['TL_HOOKS']['loadDataContainer'], ['codefog_tags.listener.tag_manager', 'onLoadDataContainer']);
} else {
    $GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['codefog_tags.listener.tag_manager', 'onLoadDataContainer'];
}
