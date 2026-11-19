<?php
namespace local_erpsso\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;

class provider implements \core_privacy\local\metadata\provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('erp', [
            'username' => 'privacy:metadata:erp:username',
        ], 'privacy:metadata:erp');
        return $collection;
    }
}