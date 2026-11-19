<?php
defined('MOODLE_INTERNAL') || die();

$templatecontext = [
    'output'            => $OUTPUT,
    'bodyattributes'    => $OUTPUT->body_attributes(['erp-theme', 'erp-popup']),
    'headercontent'     => $OUTPUT->header(),
    'maincontentblocks' => $OUTPUT->main_content(),
    'footer'            => $OUTPUT->footer(),
];

echo $OUTPUT->render_from_template('theme_erp/layout_popup', $templatecontext);
