<?php
namespace local_collegecontent\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

class create_page extends \external_api {

    /**
     * Define API parameters.
     */
    public static function execute_parameters() {
        return new \external_function_parameters([
            'courseid' => new \external_value(
                PARAM_INT,
                'Course ID'
            ),
            'sectionnum' => new \external_value(
                PARAM_INT,
                'Course section number'
            ),
            'name' => new \external_value(
                PARAM_TEXT,
                'Page name'
            ),
            'content' => new \external_value(
                PARAM_RAW,
                'Page content'
            ),
            'intro' => new \external_value(
                PARAM_RAW,
                'Page introduction',
                VALUE_DEFAULT,
                ''
            ),
        ]);
    }

    /**
     * Create a Page activity in a Moodle course.
     */
    public static function execute(
        $courseid,
        $sectionnum,
        $name,
        $content,
        $intro = ''
    ) {
        global $DB;

        // Validate incoming parameters.
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'sectionnum' => $sectionnum,
                'name' => $name,
                'content' => $content,
                'intro' => $intro,
            ]
        );

        // Validate required input.
        if (trim($params['name']) === '') {
            throw new \invalid_parameter_exception(
                'Page name cannot be empty.'
            );
        }

        if (trim($params['content']) === '') {
            throw new \invalid_parameter_exception(
                'Page content cannot be empty.'
            );
        }

        // Load course.
        $course = $DB->get_record(
            'course',
            ['id' => $params['courseid']],
            '*',
            MUST_EXIST
        );

        // Validate course context and permissions.
        $context = \context_course::instance($course->id);
        self::validate_context($context);

        require_capability(
            'moodle/course:manageactivities',
            $context
        );

        // Confirm that the requested section belongs to this course.
        $section = $DB->get_record(
            'course_sections',
            [
                'course' => $course->id,
                'section' => $params['sectionnum'],
            ],
            '*',
            MUST_EXIST
        );

        // Prepare Page activity.
        $moduleinfo = new \stdClass();

        $moduleinfo->modulename = 'page';
        $moduleinfo->module = $DB->get_field(
            'modules',
            'id',
            ['name' => 'page'],
            MUST_EXIST
        );

        $moduleinfo->course = $course->id;
        $moduleinfo->section = (int) $params['sectionnum'];
        $moduleinfo->name = $params['name'];
        $moduleinfo->intro = $params['intro'];
        $moduleinfo->introformat = FORMAT_HTML;

        $moduleinfo->content = $params['content'];
        $moduleinfo->contentformat = FORMAT_HTML;

        $moduleinfo->coursemodule = 0;
        $moduleinfo->instance = 0;
        $moduleinfo->id = 0;
        $moduleinfo->visible = 1;
        $moduleinfo->visibleoncoursepage = 1;
        $moduleinfo->cmidnumber = '';
        $moduleinfo->groupmode = 0;
        $moduleinfo->groupingid = 0;

        // Create the activity in Moodle.
        if (!function_exists('add_moduleinfo')) {
    throw new \moodle_exception(
        'add_moduleinfo() is not available after loading course/lib.php'
    );
}

$created = \add_moduleinfo($moduleinfo, $course);

        // Never report success unless Moodle returns a course module ID.
        if (empty($created->coursemodule)) {
            throw new \moodle_exception(
                'Activity creation failed: Moodle returned no course module ID.'
            );
        }

        // Return actual creation details.
        return [
            'success' => true,
            'message' => $params['name']
                . ' was added to '
                . $course->fullname,
        ];
    }

    /**
     * Define API response.
     */
    public static function execute_returns() {
        return new \external_single_structure([
            'success' => new \external_value(
                PARAM_BOOL,
                'Whether the activity was created'
            ),
            'message' => new \external_value(
                PARAM_TEXT,
                'Creation result'
            ),
        ]);
    }
}
