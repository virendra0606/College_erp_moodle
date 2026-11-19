
<?php
defined('MOODLE_INTERNAL') || die();

$templatecontext = [
    'output'            => $OUTPUT,
    'bodyattributes'    => $OUTPUT->body_attributes(['erp-theme', 'erp-maintenance']),
    'sitename'          => format_string(
        $SITE->shortname,
        true,
        ['context' => context_course::instance(SITEID)]
    ),
    'maincontentblocks' => $OUTPUT->main_content(),
];

echo $OUTPUT->render_from_template(
    'theme_erp/layout_maintenance',
    $templatecontext
);
