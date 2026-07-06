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

/**
 * Manage SMOWL file.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/smowl/lib.php');
// Required params.
$id = required_param('id', PARAM_INT);

require_course_login($id);

$context = context_course::instance($id);
$course = $DB->get_record('course', ['id' => $id]);

// Entity setting setted.
block_smowl_check_entity_navigation($course);

// Block instanced.
$blockinstance = block_smowl_check_course_block($id);
if (!$blockinstance) {
    $message = get_string('notinstancedblockincourse', 'block_smowl');
    redirect(course_get_url($course), $message, null, \core\output\notification::NOTIFY_ERROR);
}

$viewstudentcontent = has_capability('block/smowl:viewstudentcontent', $context);

$siteadmin = has_capability('moodle/site:config', context_system::instance());
$alternativerole = block_smowl_user_in_alternative_role($context);
$haspermissions = ($siteadmin && $alternativerole && $viewstudentcontent) || $viewstudentcontent;

if (!$haspermissions) {
    $message = get_string('notstudentaccesspermissions', 'block_smowl');
    redirect(course_get_url($course), $message, null, \core\output\notification::NOTIFY_ERROR);
}

// LTI.
$lticonnection = block_smowl_lti_check();
if (!$lticonnection) {
    $message = get_string('noticeemptysmowlconfig', 'block_smowl');
    redirect(course_get_url($course), $message, null, \core\output\notification::NOTIFY_ERROR);
}

// Navbar & header.
$urlparams = ['id' => $id];

$PAGE->set_url('/blocks/smowl/student.php', $urlparams);

$PAGE->set_heading($course->fullname);
$PAGE->set_title($course->shortname .': '. get_string('pluginname', 'block_smowl')
    .': '. get_string('ltistudentsmowl', 'block_smowl'));

$PAGE->navbar->add(get_string('pluginname', 'block_smowl'));
$PAGE->navbar->add(get_string('ltistudentsmowl', 'block_smowl'), new moodle_url('/blocks/smowl/manage.php', $urlparams));

$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('block_smowl');
echo $OUTPUT->header();

$PAGE->set_pagelayout('incourse');

$cmid = block_smowl_lti_check_course($id);
if (!$cmid) {
    echo $OUTPUT->notification(get_string('notlticourse', 'block_smowl'), 'danger');
    // Footer.
    echo $OUTPUT->footer();
    exit;
}
$cm = get_coursemodule_from_id('lti', $cmid, 0, false, MUST_EXIST);
$lti = $DB->get_record('lti', ['id' => $cm->instance], '*', MUST_EXIST);

$typeid = get_config('block_smowl', 'ltitypeid');

if ($typeid) {
    require_once($CFG->dirroot.'/mod/lti/locallib.php');
    $toolconfig = lti_get_type_config($typeid);
    $toolurl = $toolconfig['toolurl'];
} else {
    $toolconfig = [];
    $toolurl = $lti->toolurl;
}
$contextmodule = context_module::instance($cmid);

require_capability('mod/lti:view', $contextmodule);

if (!empty($foruserid) && (int)$foruserid !== (int)$USER->id) {
    require_capability('gradereport/grader:view', $contextmodule);
}

$url = new moodle_url('/mod/lti/view.php', ['id' => $cmid]);
$PAGE->set_url($url);

$launchcontainer = lti_get_launch_container($lti, $toolconfig);

if ($launchcontainer == LTI_LAUNCH_CONTAINER_EMBED_NO_BLOCKS) {
    $PAGE->set_pagelayout('incourse');
    $PAGE->blocks->show_only_fake_blocks(); // Disable blocks for layouts which do include pre-post blocks.
} else if ($launchcontainer == LTI_LAUNCH_CONTAINER_REPLACE_MOODLE_WINDOW) {
    if (!$forceview) {
        $url = new moodle_url('/mod/lti/launch.php', ['id' => $cmid]);
        redirect($url);
    }
} else { // Handles LTI_LAUNCH_CONTAINER_DEFAULT, LTI_LAUNCH_CONTAINER_EMBED, LTI_LAUNCH_CONTAINER_WINDOW.
    $PAGE->set_pagelayout('incourse');
}

$pagetitle = strip_tags($course->shortname.': '.format_string($lti->name));
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);

$launchurl = new moodle_url('/mod/lti/launch.php', ['id' => $cmid, 'triggerview' => 0]);
$launchurl->param('user', $USER->id);;
unset($SESSION->lti_initiatelogin_status);
if (($launchcontainer == LTI_LAUNCH_CONTAINER_WINDOW)) {
    if (!$forceview) {
        echo "<script language=\"javascript\">//<![CDATA[\n";
        echo "window.open('{$launchurl->out(true)}','lti-$cmid');";
        echo "//]]\n";
        echo "</script>\n";
        echo "<p>".get_string("basiclti_in_new_window", "lti")."</p>\n";
    }
    echo html_writer::start_tag('p');
    echo html_writer::link($launchurl->out(false), get_string("basiclti_in_new_window_open", "lti"), ['target' => '_blank']);
    echo html_writer::end_tag('p');
} else {
    $content = '';
    // Build the allowed URL, since we know what it will be from $lti->toolurl,
    // If the specified toolurl is invalid the iframe won't load, but we still want to avoid parse related errors here.
    // So we set an empty default allowed url, and only build a real one if the parse is successful.
    $ltiallow = '';
    $urlparts = parse_url($toolurl);
    if ($urlparts && array_key_exists('scheme', $urlparts) && array_key_exists('host', $urlparts)) {
        $ltiallow = $urlparts['scheme'] . '://' . $urlparts['host'];
        // If a port has been specified we append that too.
        if (array_key_exists('port', $urlparts)) {
            $ltiallow .= ':' . $urlparts['port'];
        }
    }

    // Request the launch content with an iframe tag.
    $urlstudentview = get_config('block_smowl', 'urlstudentview');
    $parts = explode('/', $urlstudentview);
    $register = implode('/', array_slice($parts, 0, 3));

    $attributes = [];
    $attributes['id'] = "contentframe";
    $attributes['height'] = '600px';
    $attributes['width'] = '100%';
    $attributes['src'] = $launchurl;
    $attributes['allow'] = "geolocation *; microphone *; display-capture *; camera *; midi *; encrypted-media *; fullscreen *; clipboard-read *; clipboard-write *;";
    $attributes['allowfullscreen'] = 1;
    $iframehtml = html_writer::tag('iframe', $content, $attributes);
    echo $iframehtml;


    // Output script to make the iframe tag be as large as possible.
    $resize = '
        <script type="text/javascript">
        //<![CDATA[
            YUI().use("node", "event", function(Y) {
                var doc = Y.one("body");
                var frame = Y.one("#contentframe");
                var padding = 15; //The bottom of the iframe wasn\'t visible on some themes. Probably because of border widths, etc.
                var lastHeight;
                var resize = function(e) {
                    var viewportHeight = doc.get("winHeight");
                    if(lastHeight !== Math.min(doc.get("docHeight"), viewportHeight)){
                        frame.setStyle("height", viewportHeight - frame.getY() - padding + "px");
                        lastHeight = Math.min(doc.get("docHeight"), doc.get("winHeight"));
                    }
                };

                resize();

                Y.on("windowresize", resize);
            });
        //]]
        </script>
';

    echo $resize;
}

// Footer.
echo $OUTPUT->footer();
