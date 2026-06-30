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
 * Test Typeform API connection
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/mod/typeform/classes/typeform_api.php');
require_once($CFG->dirroot . '/mod/typeform/classes/output/test_connection.php');
require_once($CFG->dirroot . '/mod/typeform/classes/output/renderer.php');

// Require admin access
require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/mod/typeform/test_connection.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('testconnection', 'typeform'));
$PAGE->set_heading(get_string('testconnection', 'typeform'));
$PAGE->set_pagelayout('admin');

// Get configuration
$apitoken = get_config('typeform', 'apitoken');
$allowedworkspaces = get_config('typeform', 'allowedworkspaces');

// Start output
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('testconnection', 'typeform'));

// Check if API token is configured
if (empty($apitoken)) {
    $tokenmasked = '-';
    $workspacesdisplay = get_string('allworkspaces', 'typeform');
    $testresults = [];
    $sampleforms = [];
    $formcount = 0;
    $returnurl = new moodle_url('/admin/settings.php', ['section' => 'modsettingtypeform']);

    $testconnection = new \mod_typeform\output\test_connection(
        $tokenmasked,
        $workspacesdisplay,
        $testresults,
        $sampleforms,
        $formcount,
        false,
        $returnurl->out(false)
    );

    $renderer = $PAGE->get_renderer('mod_typeform');
    echo $renderer->render_test_connection($testconnection);
    echo $OUTPUT->footer();
    exit;
}

// Prepare data
$tokenmasked = !empty($apitoken) ? substr($apitoken, 0, 8) . '...' . substr($apitoken, -4) : '-';
$workspacesdisplay = !empty($allowedworkspaces) ? $allowedworkspaces : get_string('allworkspaces', 'typeform');

$testresults = [];
$sampleforms = [];
$formcount = 0;
$success = false;

// Test 1: Basic API connection
$testconnection = \mod_typeform\typeform_api::test_connection();
if ($testconnection) {
    $testresults[] = [
        'test' => get_string('testingconnection', 'typeform'),
        'result' => true,
        'message' => get_string('connectionsuccessful', 'typeform'),
    ];
    $success = true;
} else {
    $testresults[] = [
        'test' => get_string('testingconnection', 'typeform'),
        'result' => false,
        'message' => get_string('connectionfailed', 'typeform'),
    ];
}

// Test 2: Get forms
if ($success) {
    $forms = \mod_typeform\typeform_api::get_all_forms();

    if ($forms !== false && is_array($forms)) {
        $formcount = count($forms);
        if ($formcount > 0) {
            $testresults[] = [
                'test' => get_string('testingforms', 'typeform'),
                'result' => true,
                'count' => $formcount,
                'message' => get_string('formsfound', 'typeform', $formcount),
            ];

            // Get sample forms (first 5)
            $samplecount = min(5, $formcount);
            for ($i = 0; $i < $samplecount; $i++) {
                $form = $forms[$i];
                $sampleform = [
                    'title' => $form['title'],
                ];
                if (!empty($form['workspace'])) {
                    $sampleform['workspace'] = $form['workspace'];
                }
                $sampleforms[] = $sampleform;
            }
        } else {
            $testresults[] = [
                'test' => get_string('testingforms', 'typeform'),
                'result' => true,
                'count' => 0,
                'message' => get_string('noformsfound', 'typeform'),
                'warning' => true,
            ];
        }
    } else {
        $testresults[] = [
            'test' => get_string('testingforms', 'typeform'),
            'result' => false,
            'message' => get_string('errorloadingforms', 'typeform'),
        ];
    }
}

// Test 3: Workspace validation (if configured)
if ($success && !empty($allowedworkspaces)) {
    $workspaces = \mod_typeform\typeform_api::get_allowed_workspaces();
    $invalidworkspaces = [];

    foreach ($workspaces as $workspace) {
        $result = \mod_typeform\typeform_api::get_forms($workspace['id'], '', 1, 1);
        if ($result === false || !isset($result['forms'])) {
            $invalidworkspaces[] = $workspace['id'];
        }
    }

    if (count($invalidworkspaces) == 0) {
        $testresults[] = [
            'test' => get_string('testingworkspaces', 'typeform'),
            'result' => true,
            'message' => get_string('allworkspacesvalid', 'typeform', count($workspaces)),
        ];
    } else {
        $testresults[] = [
            'test' => get_string('testingworkspaces', 'typeform'),
            'result' => false,
            'message' => get_string('someworkspacesinvalid', 'typeform', implode(', ', $invalidworkspaces)),
            'warning' => true,
        ];
    }
}

// Create output object
$returnurl = new moodle_url('/admin/settings.php', ['section' => 'modsettingtypeform']);
$testconnection = new \mod_typeform\output\test_connection(
    $tokenmasked,
    $workspacesdisplay,
    $testresults,
    $sampleforms,
    $formcount,
    true,
    $returnurl->out(false)
);

// Render using template
$renderer = $PAGE->get_renderer('mod_typeform');
echo $renderer->render_test_connection($testconnection);

echo $OUTPUT->footer();
