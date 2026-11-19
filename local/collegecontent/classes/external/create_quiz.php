<?php
namespace local_collegecontent\external;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/externallib.php');
require_once($GLOBALS['CFG']->dirroot . '/course/modlib.php');

class create_quiz extends \external_api {

    /**
     * Define parameters accepted by the external function.
     */
    public static function execute_parameters(): \external_function_parameters {
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
                'Quiz name'
            ),
            'intro' => new \external_value(
                PARAM_RAW,
                'Quiz description',
                VALUE_DEFAULT,
                ''
            ),
            'timeopen' => new \external_value(
                PARAM_INT,
                'Quiz opening Unix timestamp'
            ),
            'timeclose' => new \external_value(
                PARAM_INT,
                'Quiz closing Unix timestamp'
            ),
            'timelimit' => new \external_value(
                PARAM_INT,
                'Time limit in seconds; zero means unlimited',
                VALUE_DEFAULT,
                0
            ),
            'attempts' => new \external_value(
                PARAM_INT,
                'Allowed attempts; zero means unlimited',
                VALUE_DEFAULT,
                1
            ),
            'grade' => new \external_value(
                PARAM_FLOAT,
                'Maximum quiz grade',
                VALUE_DEFAULT,
                100.0
            ),
        ]);
    }

    /**
     * Create the quiz activity.
     */
    public static function execute(
        $courseid,
        $sectionnum,
        $name,
        $intro = '',
        $timeopen = 0,
        $timeclose = 0,
        $timelimit = 0,
        $attempts = 1,
        $grade = 100.0
    ): array {
        global $DB;

        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'courseid' => $courseid,
                'sectionnum' => $sectionnum,
                'name' => $name,
                'intro' => $intro,
                'timeopen' => $timeopen,
                'timeclose' => $timeclose,
                'timelimit' => $timelimit,
                'attempts' => $attempts,
                'grade' => $grade,
            ]
        );

        // Validate course and permissions.
        $course = get_course($params['courseid']);
        $context = \context_course::instance($course->id);

        self::validate_context($context);

        require_capability(
            'moodle/course:manageactivities',
            $context
        );

        // Validate quiz details.
        if (trim($params['name']) === '') {
            throw new \invalid_parameter_exception(
                'Quiz name cannot be empty.'
            );
        }

        if ($params['sectionnum'] < 0) {
            throw new \invalid_parameter_exception(
                'Section number cannot be negative.'
            );
        }

        // Both dates are mandatory.
        if (
            $params['timeopen'] <= 0 ||
            $params['timeclose'] <= 0
        ) {
            throw new \invalid_parameter_exception(
                'Quiz opening and closing times are required.'
            );
        }

        if ($params['timeclose'] <= $params['timeopen']) {
            throw new \invalid_parameter_exception(
                'Quiz closing time must be later than opening time.'
            );
        }

        if ($params['timelimit'] < 0) {
            throw new \invalid_parameter_exception(
                'Time limit cannot be negative.'
            );
        }

        // Moodle uses 0 to mean unlimited attempts.
        if ($params['attempts'] < 0 || $params['attempts'] > 10) {
            throw new \invalid_parameter_exception(
                'Allowed attempts must be between 0 and 10.'
            );
        }

        if ($params['grade'] <= 0) {
            throw new \invalid_parameter_exception(
                'Maximum grade must be greater than zero.'
            );
        }

        // Validate the requested section exists in this course.
        $section = $DB->get_record(
            'course_sections',
            [
                'course' => $course->id,
                'section' => $params['sectionnum'],
            ],
            '*',
            IGNORE_MISSING
        );

        if (!$section) {
            throw new \invalid_parameter_exception(
                'The selected section does not exist in this course.'
            );
        }

        // Get the Quiz module.
        $module = $DB->get_record(
            'modules',
            ['name' => 'quiz'],
            '*',
            MUST_EXIST
        );

        // Prepare the activity information for Moodle.
        $moduleinfo = new \stdClass();

        $moduleinfo->modulename = 'quiz';
        $moduleinfo->module = $module->id;
        $moduleinfo->course = $course->id;
        $moduleinfo->section = $params['sectionnum'];
        $moduleinfo->coursemodule = 0;

        $moduleinfo->visible = 1;
        $moduleinfo->visibleoncoursepage = 1;

        $moduleinfo->name = $params['name'];
        $moduleinfo->intro = $params['intro'];
        $moduleinfo->introformat = FORMAT_HTML;

        // Mandatory quiz availability dates.
        $moduleinfo->timeopen = $params['timeopen'];
        $moduleinfo->timeclose = $params['timeclose'];

        // Time limit in seconds; zero means unlimited.
        $moduleinfo->timelimit = $params['timelimit'];

        // Grading configuration.
        $moduleinfo->grade = $params['grade'];
        $moduleinfo->sumgrades = 0;

        // Attempt configuration.
        $moduleinfo->attempts = $params['attempts'];
        $moduleinfo->grademethod = 1;

        // Basic quiz settings.
        $moduleinfo->preferredbehaviour = 'deferredfeedback';
        $moduleinfo->questionsperpage = 1;
        $moduleinfo->navmethod = 'free';
        $moduleinfo->shuffleanswers = 1;
        $moduleinfo->overduehandling = 'autosubmit';
        $moduleinfo->graceperiod = 0;
        $moduleinfo->decimalpoints = 2;
        $moduleinfo->questiondecimalpoints = -1;
        $moduleinfo->showuserpicture = 0;
        $moduleinfo->showblocks = 0;

        // Review settings.
        $moduleinfo->reviewattempt = 0;
        $moduleinfo->reviewcorrectness = 0;
        $moduleinfo->reviewmarks = 0;
        $moduleinfo->reviewspecificfeedback = 0;
        $moduleinfo->reviewgeneralfeedback = 0;
        $moduleinfo->reviewrightanswer = 0;
        $moduleinfo->reviewoverallfeedback = 0;
        $moduleinfo->quizpassword = '';

        if ($moduleinfo->quizpassword === null) {
    throw new \moodle_exception('Quiz password is NULL before add_moduleinfo');
}

        // Create the quiz using Moodle's module API.
    $created = \add_moduleinfo($moduleinfo, $course);

        // Return the created activity details.
        return [
            'success' => true,
            'cmid' => (int) $created->coursemodule,
            'instanceid' => (int) $created->instance,
            'name' => $params['name'],
        ];
    }

    /**
     * Define the response structure.
     */
    public static function execute_returns(): \external_single_structure {
        return new \external_single_structure([
            'success' => new \external_value(
                PARAM_BOOL,
                'Whether the quiz was created'
            ),
            'cmid' => new \external_value(
                PARAM_INT,
                'Course module ID'
            ),
            'instanceid' => new \external_value(
                PARAM_INT,
                'Quiz instance ID'
            ),
            'name' => new \external_value(
                PARAM_TEXT,
                'Quiz name'
            ),
        ]);
    }
}
