<?php

/**
 * Backend modules
 */
$GLOBALS['BE_MOD']['content']['cfg_tags'] = [
    'tables' => ['tl_cfg_tag'],
];

/**
 * Backend widgets
 */
$GLOBALS['BE_FFL']['cfgTags'] = Codefog\TagsBundle\Widget\TagsWidget::class;

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_cfg_tag'] = \Codefog\TagsBundle\Model\TagModel::class;

/**
 * Hooks
 */
if (is_array($GLOBALS['TL_HOOKS']['loadDataContainer'])) {
    array_unshift($GLOBALS['TL_HOOKS']['loadDataContainer'], ['cfg_tags.listener.tag_manager', 'onLoadDataContainer']);
} else {
    $GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['cfg_tags.listener.tag_manager', 'onLoadDataContainer'];
}
