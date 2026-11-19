<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/erpsso:logoutusers' => [
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [],
    ],
];