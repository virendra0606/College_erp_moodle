<?php
defined('MOODLE_INTERNAL') || die();

global $PAGE, $OUTPUT;

$collegename = (string)($PAGE->theme->settings->collegename ?? 'College ERP');
$erpurl = rtrim((string)($PAGE->theme->settings->erpurl ?? ''), '/');

// Render the Moodle login form/content once.
$maincontent = $OUTPUT->main_content();

$templatecontext = [
    'collegename' => $collegename,
    'erpurl' => $erpurl,
    'output' => $OUTPUT,
    'bodyattributes' => $OUTPUT->body_attributes(['erp-theme', 'erp-login']),
    'maincontent' => $maincontent,
];

echo $OUTPUT->doctype();

echo $OUTPUT->render_from_template(
    'theme_erp/layout_login',
    $templatecontext
);