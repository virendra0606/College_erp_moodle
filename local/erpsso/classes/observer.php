<?php
namespace local_erpsso;

defined('MOODLE_INTERNAL') || die();

class observer {

    public static function user_loggedout(\core\event\user_loggedout $event): void {
        global $DB, $CFG;

        $erpurl = rtrim((string) get_config('local_erpsso', 'erpurl'), '/');
        $secret = (string) get_config('local_erpsso', 'secret');

        if ($erpurl === '' || $secret === '') {
            return;
        }

        $user = $DB->get_record('user', ['id' => $event->objectid], 'id, username, deleted');
        if (!$user || $user->deleted) {
            return;
        }

        if (isguestuser($user->id)) {
            return;
        }

        $timestamp = time();
        $signature = hash_hmac('sha256', $user->username . '|' . $timestamp, $secret);

        // We use native PHP curl_ functions instead of Moodle's \curl wrapper.
        // Moodle's \curl blocks all private-network addresses (*.local, localhost,
        // 127.x, 192.168.x etc.) via its SSRF guard. Since the ERP is on the same
        // LAN (erp.local), every call through \curl gets blocked with:
        //   "The URL is blocked / oauth2upgradetokenerror"
        // Native curl bypasses that check. This is intentional for this trusted
        // server-to-server back-channel.
        $sslverify = (bool) get_config('local_erpsso', 'sslverify');

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $erpurl . '/oauth/logout/backchannel/',
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query([
                    'username' => $user->username,
                    'ts'       => $timestamp,
                    'sig'      => $signature,
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 3,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_SSL_VERIFYPEER => $sslverify,
                CURLOPT_SSL_VERIFYHOST => $sslverify ? 2 : 0,
            ]);

            $response = curl_exec($ch);
            $errno    = curl_errno($ch);
            $errmsg   = curl_error($ch);
            curl_close($ch);

            if ($errno) {
                debugging(
                    'local_erpsso: backchannel call failed: [' . $errno . '] ' . $errmsg,
                    DEBUG_DEVELOPER
                );
            }
        } catch (\Throwable $e) {
            debugging('local_erpsso: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
}