<?php
namespace local_collegecontent\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

use context_course;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

class create_url extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'sectionnum' => new external_value(PARAM_INT, 'Section number'),
            'name' => new external_value(PARAM_TEXT, 'Activity name'),
            'url' => new external_value(PARAM_URL, 'Target URL'),
            'intro' => new external_value(
                PARAM_RAW, 'Description', VALUE_DEFAULT, ''
            ),
        ]);
    }

    public static function execute(
        $courseid, $sectionnum, $name, $url, $intro = ''
    ) {
        global $DB;

        $params = self::validate_parameters(
            self::execute_parameters(),
            compact('courseid', 'sectionnum', 'name', 'url', 'intro')
        );

        $course = get_course($params['courseid']);
        $context = context_course::instance($course->id);

        self::validate_context($context);
        require_capability('moodle/course:manageactivities', $context);

        if ($params['sectionnum'] < 0 ||
            !$DB->record_exists('course_sections', [
                'course' => $course->id,
                'section' => $params['sectionnum'],
            ])) {
            throw new \invalid_parameter_exception(
                'The selected section does not exist in this course.'
            );
        }

        $moduleid = $DB->get_field(
            'modules', 'id', ['name' => 'url'], MUST_EXIST
        );

        $moduleinfo = new \stdClass();
        $moduleinfo->modulename = 'url';
        $moduleinfo->module = $moduleid;
        $moduleinfo->course = $course->id;
        $moduleinfo->section = $params['sectionnum'];
        $moduleinfo->name = $params['name'];
        $moduleinfo->intro = $params['intro'];
        $moduleinfo->introformat = FORMAT_HTML;
        $moduleinfo->externalurl = $params['url'];
        $moduleinfo->display = 0;
        $moduleinfo->printintro = 0;
        $moduleinfo->showdescription = 0;
        $moduleinfo->visible = 1;
        $moduleinfo->visibleoncoursepage = 1;
        $moduleinfo->groupmode = 0;
        $moduleinfo->groupingid = 0;
        $moduleinfo->completion = 0;

        $created = \add_moduleinfo($moduleinfo, $course);

        return [
            'success' => true,
            'cmid' => $created->coursemodule,
            'name' => $params['name'],
        ];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'name' => new external_value(PARAM_TEXT, 'Activity name'),
        ]);
    }
}