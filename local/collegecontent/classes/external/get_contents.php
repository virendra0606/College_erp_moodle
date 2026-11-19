<?php
namespace local_collegecontent\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use context_course;

class get_contents extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(
                PARAM_INT,
                'Moodle course ID'
            ),
        ]);
    }

    public static function execute(int $courseid): array {
        global $DB;

        $params = self::validate_parameters(
            self::execute_parameters(),
            ['courseid' => $courseid]
        );

        $course = $DB->get_record(
            'course',
            ['id' => $params['courseid']],
            '*',
            MUST_EXIST
        );

        $context = context_course::instance($course->id);
        self::validate_context($context);

        // The web-service token user must be allowed to view this course.
        require_capability('moodle/course:view', $context);

        $modinfo = get_fast_modinfo($course);
        $sections = [];

        foreach ($modinfo->get_section_info_all() as $sectionnum => $sectioninfo) {
            $modules = [];

            foreach ($modinfo->get_cms() as $cm) {
                if ((int) $cm->sectionnum !== (int) $sectionnum) {
                    continue;
                }

                $modules[] = [
                    'id' => (int) $cm->id,
                    'name' => $cm->name,
                    'modname' => $cm->modname,
                    'visible' => (bool) $cm->uservisible,
                    'url' => $cm->url ? $cm->url->out(false) : '',
                ];
            }

            $sections[] = [
                'sectionnum' => (int) $sectionnum,
                'name' => get_section_name($course, $sectionnum),
                'visible' => (bool) $sectioninfo->uservisible,
                'modules' => $modules,
            ];
        }

        return [
            'courseid' => (int) $course->id,
            'coursename' => $course->fullname,
            'sections' => $sections,
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'coursename' => new external_value(PARAM_TEXT, 'Course name'),
            'sections' => new external_multiple_structure(
                new external_single_structure([
                    'sectionnum' => new external_value(PARAM_INT, 'Section number'),
                    'name' => new external_value(PARAM_TEXT, 'Section name'),
                    'visible' => new external_value(PARAM_BOOL, 'Section visibility'),
                    'modules' => new external_multiple_structure(
                        new external_single_structure([
                            'id' => new external_value(PARAM_INT, 'Course module ID'),
                            'name' => new external_value(PARAM_TEXT, 'Activity name'),
                            'modname' => new external_value(PARAM_ALPHANUMEXT, 'Module type'),
                            'visible' => new external_value(PARAM_BOOL, 'Activity visibility'),
                            'url' => new external_value(PARAM_RAW, 'Moodle activity URL'),
                        ])
                    ),
                ])
            ),
        ]);
    }
}
