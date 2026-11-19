
<?php
// Theme ERP - Moodle 4.5 compatible layout.
// @package theme_erp

defined('MOODLE_INTERNAL') || die();

global $PAGE, $USER, $OUTPUT, $SITE;

// --------------------------------------------------
// Theme settings.
// --------------------------------------------------

$erpurl = rtrim(
    (string)($PAGE->theme->settings->erpurl ?? ''),
    '/'
);

$collegename = (string)(
    $PAGE->theme->settings->collegename ?? 'College ERP'
);

// --------------------------------------------------
// Logo.
// --------------------------------------------------

$logourl = '';

$fs = get_file_storage();

$files = $fs->get_area_files(
    context_system::instance()->id,
    'theme_erp',
    'logo',
    0,
    'itemid',
    false
);

if (!empty($files)) {
    $file = reset($files);

    $logourl = moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename()
    )->out(false);
}

// --------------------------------------------------
// User state.
// --------------------------------------------------

$loggedin = isloggedin() && !isguestuser();

$userfull = '';
$userinit = '?';
$profileurl = '';

if ($loggedin) {
    $userfull = fullname($USER);

    if (empty($userfull)) {
        $userfull = $USER->username;
    }

    $userinit = mb_strtoupper(
        mb_substr($userfull, 0, 1)
    );

    $profileurl = (new moodle_url(
        '/user/profile.php',
        ['id' => $USER->id]
    ))->out(false);
}

// --------------------------------------------------
// URLs.
// --------------------------------------------------

$loginurl = (new moodle_url('/login/index.php'))->out(false);

$logouturl = '';

if ($loggedin) {
    $logouturl = (new moodle_url(
        '/login/logout.php',
        ['sesskey' => sesskey()]
    ))->out(false);
}

$logoutaction = '';

if ($loggedin) {
    $logoutaction = $erpurl
        ? $erpurl . '/accounts/logout/'
        : $logouturl;
}

// --------------------------------------------------
// Secondary navigation.
// --------------------------------------------------

$hassecondary = $PAGE->has_secondary_navigation();

$secondarynavigation = false;
$overflow = false;

if ($hassecondary) {

    $tablistnav = $PAGE->has_tablist_secondary_navigation();

    $moremenu = new \core\navigation\output\more_menu(
        $PAGE->secondarynav,
        'nav-tabs',
        true,
        $tablistnav
    );

    $secondarynavigation =
        $moremenu->export_for_template($OUTPUT);

    $overflowdata =
        $PAGE->secondarynav->get_overflow_menu_data();

    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
}

// --------------------------------------------------
// Add block button.
// --------------------------------------------------

$addblockbutton = '';

if (
    !$PAGE->include_region_main_settings_in_header_actions()
    && !$hassecondary
) {
    $addblockbutton = $OUTPUT->addblockbutton();
}

// --------------------------------------------------
// Activity header.
// --------------------------------------------------

$renderer = $PAGE->get_renderer('core');

$header = $PAGE->activityheader;

$headercontent = $OUTPUT->render_from_template(
    'core/activity_header',
    $header->export_for_template($renderer)
);

// --------------------------------------------------
// Page content.
// IMPORTANT: Explicitly echo main_content().
// This registers Moodle's main content placeholder.
// --------------------------------------------------

$beforecontent = $OUTPUT->course_content_header();

ob_start();
echo $OUTPUT->main_content();
$maincontentblocks = ob_get_clean();

$aftercontent = $OUTPUT->course_content_footer();

// --------------------------------------------------
// Blocks.
// --------------------------------------------------

$blockshtml = $OUTPUT->blocks('side-pre');

$hasblocks = (
    strpos($blockshtml, 'data-block=') !== false
);

// --------------------------------------------------
// Course index.
// --------------------------------------------------

$usercourseindex = $loggedin;

$courseindex = '';

if ($usercourseindex) {
    $courseindex = core_course_drawer();
}


// --------------------------------------------------
// Moodle 4.5 Primary Navigation.
// Uses the supported Primary Navigation API.
// --------------------------------------------------

$renderer = $PAGE->get_renderer('core');

$primary = new \core\navigation\output\primary($PAGE);
$primarymenu = $primary->export_for_template($renderer);


// --------------------------------------------------
// Template context.
// --------------------------------------------------

$templatecontext = [

    // Theme.
    'collegename' => $collegename,
    'logourl' => $logourl,
    'erpurl' => $erpurl,
    'loggedin' => $loggedin,
    'loginurl' => $loginurl,

    // Moodle output.
    'output' => $OUTPUT,
    'bodyattributes' => $OUTPUT->body_attributes(['erp-theme']),

    'hassecondarynavigation' => $hassecondary,
    'secondarynavigation' => $secondarynavigation,
    'overflow' => $overflow,

    // Primary navigation.
    'primarymoremenu' => $primarymenu['moremenu'],
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    

    // User.
    'userfullname' => $userfull,
    'userinit' => $userinit,
    'userprofileurl' => $profileurl,
    'logouturl' => $logouturl,
    'logoutaction' => $logoutaction,

    // Editing.
    'haseditswitchhdr' => $PAGE->user_allowed_editing(),
    'editswitch' => $OUTPUT->edit_switch(),

    // Block button.
    'addblockbutton' => $addblockbutton,

    // Header.
    'headercontent' => $headercontent,

    // Page content.
    'beforecontent' => $beforecontent,
    'maincontentblocks' => $maincontentblocks,
    'aftercontent' => $aftercontent,

    // Blocks.
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,

    // Course index.
    'courseindex' => $courseindex,
    'usercourseindex' => $usercourseindex,
];

// --------------------------------------------------
// Custom theme JavaScript.
// --------------------------------------------------

$PAGE->requires->js_call_amd(
    'theme_boost/drawer',
    'init'
);

// --------------------------------------------------
// Render layout.
// --------------------------------------------------

echo $OUTPUT->doctype();

echo $OUTPUT->render_from_template(
    'theme_erp/layout_drawers',
    $templatecontext
);
