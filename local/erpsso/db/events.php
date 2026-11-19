<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\core\event\user_loggedout',
        'callback'  => '\local_erpsso\observer::user_loggedout',
    ],
];