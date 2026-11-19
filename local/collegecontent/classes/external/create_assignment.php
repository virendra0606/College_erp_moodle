<?php
namespace local_collegecontent\external;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->dirroot . '/course/modlib.php');
require_once($GLOBALS['CFG']->dirroot . '/mod/assign/lib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

class create_assignment extends external_api {

    /**
     * Define the external function parameters.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(
                PARAM_INT,
                'Course ID'
            ),
            'sectionnum' => new external_value(
                PARAM_INT,
                'Course section number',
                VALUE_DEFAULT,
                0
            ),
            'name' => new external_value(
                PARAM_TEXT,
                'Assignment name'
            ),
            'intro' => new external_value(
                PARAM_RAW,
                'Assignment description',
                VALUE_DEFAULT,
                ''
            ),
            'allowsubmissionsfromdate' => new external_value(
                PARAM_INT,
                'Submission start timestamp',
                VALUE_DEFAULT,
                0
            ),
            'duedate' => new external_value(
                PARAM_INT,
                'Submission due timestamp',
                VALUE_DEFAULT,
                0
            ),
            'grade' => new external_value(
                PARAM_INT,
                'Maximum grade',
                VALUE_DEFAULT,
                100
            ),
        ]);
    }

    /**
     * Create the assignment activity.
     */
    public static function execute(
        $courseid,
        $sectionnum = 0,
        $name = '',
        $intro = '',
        $allowsubmissionsfromdate = 0,
        $duedate = 0,
        $grade = 100
    ) {
        global $DB, $CFG, $USER;

        require_once($CFG->dirroot . '/course/modlib.php');
        require_once($CFG->dirroot . '/mod/assign/lib.php');

        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'sectionnum' => $sectionnum,
                'name' => $name,
                'intro' => $intro,
                'allowsubmissionsfromdate' => $allowsubmissionsfromdate,
                'duedate' => $duedate,
                'grade' => $grade,
            ]
        );

        $course = $DB->get_record(
            'course',
            ['id' => $params['courseid']],
            '*',
            MUST_EXIST
        );

        $context = \context_course::instance($course->id);

        self::validate_context($context);

        require_capability('moodle/course:manageactivities', $context);

        if (trim($params['name']) === '') {
            throw new \invalid_parameter_exception(
                'Assignment name cannot be empty.'
            );
        }

        if ((int)$params['grade'] < 0) {
            throw new \invalid_parameter_exception(
                'Grade cannot be negative.'
            );
        }

        $sectionnum = (int)$params['sectionnum'];

        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        if (!isset($sections[$sectionnum])) {
            throw new \invalid_parameter_exception(
                'The requested course section does not exist.'
            );
        }

        $moduleinfo = new \stdClass();

        // Activity module configuration.
        $moduleinfo->modulename = 'assign';
        $moduleinfo->module = $DB->get_field(
            'modules',
            'id',
            ['name' => 'assign'],
            MUST_EXIST
        );

        $moduleinfo->course = $course->id;
        $moduleinfo->section = $sectionnum;
        $moduleinfo->visible = 1;
        $moduleinfo->visibleoncoursepage = 1;

        // Assignment details.
        $moduleinfo->name = $params['name'];
        $moduleinfo->intro = $params['intro'];
        $moduleinfo->introformat = FORMAT_HTML;

        // Submission settings.
        $moduleinfo->alwaysshowdescription = 1;
        $moduleinfo->submissiondrafts = 0;
        $moduleinfo->requiresubmissionstatement = 0;
        $moduleinfo->sendnotifications = 0;
        $moduleinfo->sendlatenotifications = 0;
        $moduleinfo->sendstudentnotifications = 1;

        $moduleinfo->allowsubmissionsfromdate =
            (int)$params['allowsubmissionsfromdate'];

        $moduleinfo->duedate = (int)$params['duedate'];
        $moduleinfo->cutoffdate = 0;
        $moduleinfo->gradingduedate = 0;

        // Grading settings.
        $moduleinfo->grade = (int)$params['grade'];
        $moduleinfo->completionsubmit = 0;

        // Team submission settings.
        $moduleinfo->teamsubmission = 0;
        $moduleinfo->requireallteammemberssubmit = 0;

        // Marking settings.
        $moduleinfo->blindmarking = 0;
        $moduleinfo->markingworkflow = 0;
        $moduleinfo->markingallocation = 0;

        // Attempts settings.
        $moduleinfo->maxattempts = 1;
        $moduleinfo->attemptreopenmethod = 'none';

        // Additional module defaults.
        $moduleinfo->cmidnumber = '';
        $moduleinfo->groupmode = 0;
        $moduleinfo->groupingid = 0;
        $moduleinfo->completion = 0;
        $moduleinfo->completionview = 0;
        $moduleinfo->completionexpected = 0;
        $moduleinfo->availability = null;
        $moduleinfo->showdescription = 0;
        $moduleinfo->tags = [];

        // Let Moodle create the assignment and course module.
        $created = add_moduleinfo($moduleinfo, $course);

        if (empty($created) || empty($created->coursemodule)) {
            throw new \moodle_exception(
                'Assignment could not be created.'
            );
        }

        return [
            'success' => true,
            'courseid' => (int)$course->id,
            'cmid' => (int)$created->coursemodule,
            'instanceid' => (int)$created->instance,
            'name' => $params['name'],
            'message' => 'Assignment created successfully.',
        ];
    }

    /**
     * Define the response structure.
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(
                PARAM_BOOL,
                'Whether creation succeeded'
            ),
            'courseid' => new external_value(
                PARAM_INT,
                'Course ID'
            ),
            'cmid' => new external_value(
                PARAM_INT,
                'Course module ID'
            ),
            'instanceid' => new external_value(
                PARAM_INT,
                'Assignment instance ID'
            ),
            'name' => new external_value(
                PARAM_TEXT,
                'Assignment name'
            ),
            'message' => new external_value(
                PARAM_TEXT,
                'Result message'
            ),
        ]);
    }
}
