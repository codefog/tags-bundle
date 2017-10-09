<?php

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

$GLOBALS['TL_DCA']['tl_cfg_tag'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'notCopyable' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'alias' => 'index',
                'name,source' => 'unique',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => ['name'],
            'flag' => 1,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'source', 'total'],
            'showColumns' => true,
            'label_callback' => ['codefog_tags.listener.data_container.tag', 'generateLabel'],
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cfg_tag']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cfg_tag']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_cfg_tag']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Select
    'select' => [
        'buttons_callback' => [
            ['codefog_tags.listener.data_container.tag', 'addAliasButton'],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{name_legend},name,source,alias',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true],
        ],
        'total' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cfg_tag']['count'],
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cfg_tag']['name'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'alnum' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
        ],
        'source' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cfg_tag']['source'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options_callback' => ['codefog_tags.listener.data_container.tag', 'getSources'],
            'reference' => &$GLOBALS['TL_LANG']['tl_cfg_tag']['sourceRef'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 64, 'default' => 'NULL'],
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cfg_tag']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alias', 'maxlength' => 128, 'tl_class' => 'w50'],
            'save_callback' => [
                ['codefog_tags.listener.data_container.tag', 'generateAlias'],
            ],
            'sql' => ['type' => 'string', 'length' => 128],
        ],
    ],
];
