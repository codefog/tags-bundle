<?php

declare(strict_types=1);

use Codefog\TagsBundle\Driver;
use Contao\DataContainer;
use Doctrine\DBAL\Types\Types;

/*
 * Tags Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

$GLOBALS['TL_DCA']['tl_cfg_tag'] = [
    // Config
    'config' => [
        'dataContainer' => Driver::class,
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
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;cfg_sort,search,limit',
        ],
        'label' => [
            'fields' => ['name', 'source', 'total'],
            'showColumns' => true,
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{name_legend},name,source,alias',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => Types::INTEGER, 'unsigned' => true],
        ],
        'total' => [
            'label' => &$GLOBALS['TL_LANG']['tl_cfg_tag']['count'],
        ],
        'name' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'alnum' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'default' => ''],
        ],
        'source' => [
            'filter' => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_cfg_tag']['sourceRef'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 64, 'notnull' => false],
        ],
        'alias' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'alias', 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql' => ['type' => Types::STRING, 'length' => 128, 'default' => ''],
        ],
    ],
];
