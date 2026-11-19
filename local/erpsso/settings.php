<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_erpsso', get_string('pluginname', 'local_erpsso'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext(
        'local_erpsso/erpurl',
        get_string('erpurl', 'local_erpsso'),
        get_string('erpurl_desc', 'local_erpsso'),
        '',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_erpsso/secret',
        get_string('secret', 'local_erpsso'),
        get_string('secret_desc', 'local_erpsso'),
        ''
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_erpsso/sslverify',
        get_string('sslverify', 'local_erpsso'),
        get_string('sslverify_desc', 'local_erpsso'),
        1
    ));
}