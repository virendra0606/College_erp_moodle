<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_erpsso_logout_user' => [
        'classname'    => 'local_erpsso\external\logout_user',
        'description'  => 'Ends all Moodle sessions of a user (ERP single logout).',
        'type'         => 'write',
        'ajax'         => false,
        'capabilities' => 'local/erpsso:logoutusers',
    ],
];