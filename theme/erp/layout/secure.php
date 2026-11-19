<?php
defined('MOODLE_INTERNAL') || die();

$collegename = (string)($PAGE->theme->settings->collegename ?? 'College ERP');

$templatecontext = [
    'collegename'       => $collegename,
    'output'            => $OUTPUT,
    'bodyattributes'    => $OUTPUT->body_attributes(['erp-theme', 'erp-secure']),
    'headercontent'     => $OUTPUT->header(),
    'maincontentblocks' => $OUTPUT->main_content(),
    'footer'            => $OUTPUT->footer(),
];

echo $OUTPUT->render_from_template('theme_erp/layout_secure', $templatecontext);
