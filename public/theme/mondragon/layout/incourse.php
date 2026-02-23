<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

use theme_moove\util\settings;

defined('MOODLE_INTERNAL') || die();
global $CFG, $PAGE, $OUTPUT, $SITE;

if ($PAGE->pagetype === 'mod-scorm-player' &&
    $PAGE->pagelayout === 'incourse' &&
    $PAGE->cm->modname === 'scorm' &&
    (strpos($PAGE->url->get_path(), '/mod/scorm/player.php') !== false) &&
    get_config('theme_mondragon', 'activescormview')
) {
    $extraclasses = ['scorm-fullscreen'];
    $bodyattributes = $OUTPUT->body_attributes($extraclasses);
    $primary = new core\navigation\output\primary($PAGE);
    $renderer = $PAGE->get_renderer('core');
    $primarymenu = $primary->export_for_template($renderer);
    $header = $PAGE->activityheader;
    $headercontent = $header->export_for_template($renderer);
    $templatecontext = [
        'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
        'output' => $OUTPUT,
        'sidepreblocks' => '',
        'hasblocks' => false,
        'bodyattributes' => $bodyattributes,
        'courseindexopen' => false,
        'blockdraweropen' => false,
        'courseindex' => '',
        'primarymoremenu' => $primarymenu['moremenu'],
        'secondarymoremenu' => false,
        'mobileprimarynav' => $primarymenu['mobileprimarynav'],
        'usermenu' => $primarymenu['user'],
        'langmenu' => $primarymenu['lang'],
        'forceblockdraweropen' => false,
        'regionmainsettingsmenu' => false,
        'hasregionmainsettingsmenu' => false,
        'overflow' => '',
        'headercontent' => $headercontent,
        'addblockbutton' => false,
        'backcourseurl' => (new moodle_url('/course/view.php', ['id' => $PAGE->cm->course]))->out()
    ];

    $themesettings = new settings();

    $templatecontext = array_merge($templatecontext, $themesettings->footer());

    echo $OUTPUT->render_from_template('theme_mondragon/scormfullscreen', $templatecontext);
} else {
    include($CFG->dirroot . '/theme/moove/layout/course.php');
}