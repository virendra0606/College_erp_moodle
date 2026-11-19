<?php
namespace local_collegecontent\external;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/externallib.php');

class create_forum extends \external_api {

   
public static function execute_parameters(): \external_function_parameters {
    return new \external_function_parameters([
        'courseid' => new \external_value(
            PARAM_INT,
            'Course ID'
        ),
        'sectionnum' => new \external_value(
            PARAM_INT,
            'Section number'
        ),
        'name' => new \external_value(
            PARAM_TEXT,
            'Forum name'
        ),
        'intro' => new \external_value(
            PARAM_RAW,
            'Forum description',
            VALUE_DEFAULT,
            ''
        ),
        'forumtype' => new \external_value(
            PARAM_ALPHANUMEXT,
            'Forum type',
            VALUE_DEFAULT,
            'general'
        ),
    ]);
}


    public static function execute(
        $courseid,
    $sectionnum,
    $name,
    $intro = '',
    $forumtype = 'general'
    ): array {
        global $CFG, $DB;

        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'sectionnum' => $sectionnum,
                'name' => $name,
                'intro' => $intro,
                'forumtype' => $forumtype,
            ]
        );

        $course = get_course($params['courseid']);
        $context = \context_course::instance($course->id);

        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        if (trim($params['name']) === '') {
            throw new \invalid_parameter_exception(
                'Forum name cannot be empty.'
            );
        }

        if ($params['sectionnum'] < 0) {
            throw new \invalid_parameter_exception(
                'Section number cannot be negative.'
            );
        }

        $allowedtypes = [
            'general',
            'single',
            'eachuser',
            'qanda',
            'blog',
            'standard',
        ];

        if (!in_array($params['forumtype'], $allowedtypes, true)) {
            throw new \invalid_parameter_exception(
                'Invalid forum type.'
            );
        }

        require_once($CFG->dirroot . '/course/modlib.php');

        $module = $DB->get_record(
            'modules',
            ['name' => 'forum'],
            '*',
            MUST_EXIST
        );

        $moduleinfo = new \stdClass();
        $moduleinfo->modulename = 'forum';
        $moduleinfo->module = $module->id;
        $moduleinfo->course = $course->id;
        $moduleinfo->section = $params['sectionnum'];
        $moduleinfo->coursemodule = 0;
        $moduleinfo->visible = 1;
        $moduleinfo->visibleoncoursepage = 1;
        $moduleinfo->name = $params['name'];
        $moduleinfo->intro = $params['intro'];
        $moduleinfo->introformat = FORMAT_HTML;
        $moduleinfo->type = $params['forumtype'];

        // Standard forum settings.
        $moduleinfo->assessed = 0;
        $moduleinfo->scale = 0;
        $moduleinfo->maxbytes = 0;
        $moduleinfo->maxattachments = 9;
        $moduleinfo->forcesubscribe = 0;
        $moduleinfo->trackingtype = 1;
        $moduleinfo->displaywordcount = 0;
        $moduleinfo->lockdiscussionafter = 0;
        $moduleinfo->duedate = 0;
        $moduleinfo->cutoffdate = 0;
        $moduleinfo->completion = 0;

        $created = \add_moduleinfo($moduleinfo, $course);

        return [
            'success' => true,
            'cmid' => (int) $created->coursemodule,
            'instanceid' => (int) $created->instance,
            'name' => $params['name'],
        ];
    }

    public static function execute_returns(): \external_single_structure {
        return new \external_single_structure([
            'success' => new \external_value(
                PARAM_BOOL,
                'Creation status'
            ),
            'cmid' => new \external_value(
                PARAM_INT,
                'Course module ID'
            ),
            'instanceid' => new \external_value(
                PARAM_INT,
                'Forum instance ID'
            ),
            'name' => new \external_value(
                PARAM_TEXT,
                'Forum name'
            ),
        ]);
    }
}
