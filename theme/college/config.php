<?php
defined('MOODLE_INTERNAL') || die();

$THEME->name = 'college';

// Extend Moodle's default Boost theme.
$THEME->parents = ['boost'];

// Load our custom stylesheet from the style directory.
$THEME->sheets = ['custom'];

// Keep Moodle's standard renderer system.
$THEME->rendererfactory = 'theme_overridden_renderer_factory';

// Disable the old docking interface.
$THEME->enable_dock = false;

// No YUI CSS modules are required.
$THEME->yuicssmodules = [];

// Show this theme in the theme selector.
$THEME->hidefromselector = false;