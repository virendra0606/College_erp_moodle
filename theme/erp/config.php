<?php
defined('MOODLE_INTERNAL') || die();

$THEME->name     = 'erp';
$THEME->fullname = 'College ERP Theme';
$THEME->parents  = ['boost'];
$THEME->sheets   = [];

$THEME->scss = function ($theme) {
    return theme_erp_get_main_scss_content($theme);
};
$THEME->prescsscallback   = 'theme_erp_get_pre_scss';
$THEME->extrascsscallback = 'theme_erp_get_extra_scss';

// layout 'file' path is relative to theme root — must include subfolder.
$THEME->layouts = [
    'base' => [
        'file'          => 'drawers.php',
        'regions'       => [],
        'defaultregion' => '',
    ],
    'standard' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
        'options'       => ['langmenu' => true],
    ],
    'course' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
        'options'       => ['langmenu' => true],
    ],
    'coursecategory' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
        'options'       => ['langmenu' => true],
    ],
    'incourse' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
        'options'       => ['langmenu' => true],
    ],
    'frontpage' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
        'options'       => ['nonavbar' => false],
    ],
    'admin' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'mydashboard' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
        'options'       => ['nonavbar' => false, 'langmenu' => true],
    ],
    'mypublic' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
        'options'       => ['langmenu' => true],
    ],
    'login' => [
        'file'          => 'login.php',
        'regions'       => [],
        'defaultregion' => '',
        'options'       => ['langmenu' => true],
    ],
    'popup' => [
        'file'          => 'popup.php',
        'regions'       => [],
        'defaultregion' => '',
        'options'       => ['nofooter' => true, 'nonavbar' => true],
    ],
    'frametop' => [
        'file'          => 'popup.php',
        'regions'       => [],
        'defaultregion' => '',
        'options'       => ['nofooter' => true],
    ],
    'embedded' => [
        'file'          => 'embedded.php',
        'regions'       => [],
        'defaultregion' => '',
    ],
    'maintenance' => [
        'file'          => 'maintenance.php',
        'regions'       => [],
        'defaultregion' => '',
    ],
    'print' => [
        'file'          => 'popup.php',
        'regions'       => [],
        'defaultregion' => '',
        'options'       => ['nofooter' => true, 'nonavbar' => true, 'noblocks' => true],
    ],
    'redirect' => [
        'file'          => 'embedded.php',
        'regions'       => [],
        'defaultregion' => '',
    ],
    'report' => [
        'file'          => 'drawers.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'secure' => [
        'file'          => 'secure.php',
        'regions'       => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
];

$THEME->enable_dock          = false;
$THEME->requiredblocks       = '';
$THEME->addblockposition     = BLOCK_ADDBLOCK_POSITION_FLATNAV;
$THEME->usescourseindex      = true;
$THEME->haseditswitch        = true;
$THEME->activityheaderconfig = [
    'notitle'      => false,
    'nocompletion' => false,
    'nointro'      => false,
];