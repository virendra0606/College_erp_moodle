<?php
namespace local_collegecontent\external;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->dirroot . '/course/modlib.php');
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/resource/lib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use context_course;
use moodle_exception;

class create_resource extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(
                PARAM_INT,
                'Moodle course ID'
            ),
            'sectionnum' => new external_value(
                PARAM_INT,
                'Course section number, starting from 0'
            ),
            'draftitemid' => new external_value(
                PARAM_INT,
                'Draft file area item ID returned by upload.php'
            ),
            'name' => new external_value(
                PARAM_TEXT,
                'Resource name'
            ),
            'intro' => new external_value(
                PARAM_RAW,
                'Resource description',
                VALUE_DEFAULT,
                ''
            ),
        ]);
    }

    public static function execute(
        int $courseid,
        int $sectionnum,
        int $draftitemid,
        string $name,
        string $intro = ''
    ): array {
        global $DB, $CFG;

        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'sectionnum' => $sectionnum,
                'draftitemid' => $draftitemid,
                'name' => $name,
                'intro' => $intro,
            ]
        );

        $course = $DB->get_record(
            'course',
            ['id' => $params['courseid']],
            '*',
            MUST_EXIST
        );

        $context = context_course::instance($course->id);
        self::validate_context($context);

        // The web-service token user must have permission in this course.
        require_capability('moodle/course:manageactivities', $context);

        if (trim($params['name']) === '') {
            throw new moodle_exception(
                'invalidparameter',
                'error',
                '',
                'Resource name cannot be empty.'
            );
        }

        if ($params['sectionnum'] < 0) {
            throw new moodle_exception(
                'invalidparameter',
                'error',
                '',
                'Section number cannot be negative.'
            );
        }

        // Ensure the requested section exists in this course.
        $section = $DB->get_record(
            'course_sections',
            [
                'course' => $course->id,
                'section' => $params['sectionnum'],
            ],
            '*',
            MUST_EXIST
        );

        // Verify the draft file belongs to the web-service user's draft area.
        $draftfiles = get_file_storage()->get_area_files(
            \context_user::instance($GLOBALS['USER']->id)->id,
            'user',
            'draft',
            $params['draftitemid'],
            'filename',
            false
        );

        if (empty($draftfiles)) {
            throw new moodle_exception(
                'nofiles',
                'error',
                '',
                'No uploaded files were found in the specified draft area.'
            );
        }

        $moduleid = $DB->get_field(
            'modules',
            'id',
            ['name' => 'resource'],
            MUST_EXIST
        );

        // Prepare the standard Moodle resource module data.
        $moduleinfo = new \stdClass();
        $moduleinfo->modulename = 'resource';
        $moduleinfo->module = $moduleid;
        $moduleinfo->course = $course->id;
        $moduleinfo->section = $params['sectionnum'];
        $moduleinfo->name = $params['name'];
        $moduleinfo->intro = $params['intro'];
        $moduleinfo->introformat = FORMAT_HTML;
        $moduleinfo->files = $params['draftitemid'];
        $moduleinfo->display = 0;
        $moduleinfo->printintro = 1;
        $moduleinfo->visible = 1;

        // Moodle creates the course module and moves the draft file.
        $created = add_moduleinfo($moduleinfo, $course);

        return [
            'success' => true,
            'courseid' => (int) $course->id,
            'coursemoduleid' => (int) $created->coursemodule,
            'instanceid' => (int) $created->instance,
            'name' => $params['name'],
            'message' => 'Course resource created successfully.',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether creation succeeded'),
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'coursemoduleid' => new external_value(PARAM_INT, 'Course module ID'),
            'instanceid' => new external_value(PARAM_INT, 'Resource instance ID'),
            'name' => new external_value(PARAM_TEXT, 'Resource name'),
            'message' => new external_value(PARAM_TEXT, 'Result message'),
        ]);
    }
}
