<?php
defined('MOODLE_INTERNAL') || die();

$functions = [

    'local_collegecontent_create_resource' => [
        'classname' => '\local_collegecontent\external\create_resource',
        'methodname' => 'execute',
        'description' => 'Create a course file resource from an uploaded draft file.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => 'moodle/course:manageactivities',
    ],

    'local_collegecontent_get_contents' => [
        'classname' => '\local_collegecontent\external\get_contents',
        'methodname' => 'execute',
        'description' => 'Get course sections and activities for the Django interface.',
        'type' => 'read',
        'ajax' => false,
        'capabilities' => 'moodle/course:view',
    ],

    'local_collegecontent_create_page' => [
        'classname' => '\local_collegecontent\external\create_page',
        'methodname' => 'execute',
        'description' => 'Create a Page activity in a course.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => 'moodle/course:manageactivities',
    ],

    'local_collegecontent_create_url' => [
        'classname' => '\local_collegecontent\external\create_url',
        'methodname' => 'execute',
        'description' => 'Create a URL activity in a course.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => 'moodle/course:manageactivities',
    ],

    'local_collegecontent_create_assignment' => [
        'classname' => '\local_collegecontent\external\create_assignment',
        'methodname' => 'execute',
        'description' => 'Create an Assignment activity in a course.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => 'moodle/course:manageactivities',
    ],

    'local_collegecontent_create_quiz' => [
        'classname' => '\local_collegecontent\external\create_quiz',
        'methodname' => 'execute',
        'description' => 'Create a Quiz activity in a course.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => 'moodle/course:manageactivities',
    ],

    'local_collegecontent_create_forum' => [
        'classname' => '\local_collegecontent\external\create_forum',
        'methodname' => 'execute',
        'description' => 'Create a Forum activity in a course.',
        'type' => 'write',
        'ajax' => false,
        'capabilities' => 'moodle/course:manageactivities',
    ],
];
