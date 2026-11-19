<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext(
        'theme_erp/collegename',
        get_string('collegename', 'theme_erp'),
        'Name displayed in the ERP theme header.',
        'College ERP',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'theme_erp/erpurl',
        get_string('erpurl', 'theme_erp'),
        'Full URL of your existing College ERP portal.',
        '',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configstoredfile(
        'theme_erp/logo',
        get_string('logo', 'theme_erp'),
        'Upload your College ERP logo.',
        'logo',
        0,
        [
            'maxfiles' => 1,
            'accepted_types' => ['.png', '.jpg', '.jpeg', '.svg'],
        ]
    ));
}