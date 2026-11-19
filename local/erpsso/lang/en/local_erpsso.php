<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']    = 'ERP single logout';

$string['erpurl']        = 'ERP base URL';
$string['erpurl_desc']   = 'Base URL of the ERP, e.g. https://erp.local (no trailing slash). '
                         . 'LAN/localhost addresses work because this plugin uses native PHP curl, '
                         . 'not Moodle\'s \\curl wrapper.';

$string['secret']        = 'Shared secret';
$string['secret_desc']   = 'Must match SSO_BACKCHANNEL_SECRET in the ERP .env file exactly.';

$string['sslverify']     = 'Verify SSL certificate';
$string['sslverify_desc'] = 'Leave ticked in production. '
                          . 'Untick ONLY on a dev server where the ERP runs on http:// or uses a self-signed certificate.';

$string['erpsso:logoutusers']            = 'Log users out of Moodle on behalf of the ERP';
$string['privacy:metadata:erp']          = 'When a user logs out of Moodle their username is sent to the ERP '
                                          . 'so the ERP can end that user\'s sessions.';
$string['privacy:metadata:erp:username'] = 'The Moodle username of the user who logged out.';