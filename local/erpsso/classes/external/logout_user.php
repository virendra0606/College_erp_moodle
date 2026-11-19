<?php
namespace local_erpsso\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

class logout_user extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'username' => new external_value(PARAM_USERNAME, 'Moodle username of the user to log out'),
        ]);
    }

    public static function execute(string $username): array {
        global $DB, $CFG;

        $params  = self::validate_parameters(self::execute_parameters(), ['username' => $username]);
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/erpsso:logoutusers', $context);

        $user = $DB->get_record('user', [
            'username'   => $params['username'],
            'mnethostid' => $CFG->mnet_localhost_id,
            'deleted'    => 0,
        ], 'id, username');

        if (!$user) {
            return ['killed' => false];
        }

        if (is_siteadmin($user->id)) {
            throw new \invalid_parameter_exception(
                'Site administrators cannot be logged out through this service.'
            );
        }

        \core\session\manager::kill_user_sessions($user->id);
        return ['killed' => true];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'killed' => new external_value(PARAM_BOOL, 'True if sessions were ended'),
        ]);
    }
}