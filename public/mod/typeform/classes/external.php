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
 * External API for mod_typeform
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_typeform;

use completion_info;
use core\context\module;
use core\exception\moodle_exception;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_multiple_structure;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/mod/typeform/classes/typeform_api.php');
require_once($CFG->dirroot . '/mod/typeform/lib.php');


/**
 * External functions for mod_typeform
 */
class mod_typeform_external extends \core_external\external_api
{
    /**
     * Returns description of get_forms parameters
     * @return external_function_parameters
     */
    public static function get_forms_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Get list of Typeform forms
     *
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     * @throws \require_login_exception
     * @throws \required_capability_exception
     */
    public static function get_forms() {

        // Check user has capability to add/edit typeform activities
        require_login();
        $context = \context_system::instance();
        require_capability('mod/typeform:addinstance', $context);

        // Use get_all_forms() to get all forms with pagination and workspace handling
        $forms = \mod_typeform\typeform_api::get_all_forms();

        // get_all_forms() always returns an array (may be empty)
        if (!is_array($forms)) {
            return [
                'success' => false,
                'message' => get_string('errorloadingforms', 'typeform'),
                'forms' => [],
            ];
        }

        return [
            'success' => true,
            'forms' => $forms,
            'message' => '',
        ];
    }

    /**
     * Returns description of get_forms result value
     * @return \external_description
     */
    public static function get_forms_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the request was successful'),
            'message' => new external_value(PARAM_TEXT, 'Error message if any'),
            'forms' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_TEXT, 'Typeform ID'),
                    'title' => new external_value(PARAM_TEXT, 'Typeform title'),
                    'workspace' => new external_value(PARAM_TEXT, 'Workspace name', VALUE_OPTIONAL),
                ])
            ),
        ]);
    }

    /**
     * Returns description of mark_complete parameters
     * @return external_function_parameters
     */
    public static function mark_complete_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    /**
     * Mark typeform activity as complete for current user
     *
     * @param int $cmid Course module ID
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     * @throws \require_login_exception
     * @throws \required_capability_exception
     */
    public static function mark_complete($cmid) {
        global $USER, $DB;

        $params = self::validate_parameters(self::mark_complete_parameters(), ['cmid' => $cmid]);

        $cm = get_coursemodule_from_id('typeform', $params['cmid'], 0, false, MUST_EXIST);
        $typeform = $DB->get_record('typeform', ['id' => $cm->instance]);
        $context = module::instance($cm->id);
        require_login($cm->course, false, $cm);
        require_capability('mod/typeform:view', $context);

        // Check if already completed.
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $completion = new completion_info($course);
        $completiondata = $completion->get_data($cm, false, $USER->id);
        if (!$completion->is_enabled($cm)) {
            return [
                'success' => true,
                'message' => get_string('completionnotenabled', 'completion'),
            ];
        }
        if (!$typeform->completionsubmit) {
            // Add submission record.
            $submissiondata = [
                    'typeform' => $cm->instance,
                    'userid' => $USER->id,
                    'submitted' => 1,
                    'timemodified' => time(),
                    'timecreated' => time(),
                    'usermodified' => $USER->id,
            ];
            typeform_add_typeformsubmission_record($submissiondata);
            // Trigger event.
            self::add_event('attempt_finished', $cmid);
            return [
                'success' => true,
                'message' => get_string('completionnotenabled', 'completion'),
            ];
        }

        if ($completiondata->completionstate == COMPLETION_COMPLETE) {
            return [
                'success' => true,
                'message' => get_string('alreadycompleted', 'typeform'),
            ];
        }

        // Mark as complete.
        $submissiondata = [
                'typeform' => $cm->instance,
                'userid' => $USER->id,
                'submitted' => 1,
                'timemodified' => time(),
                'timecreated' => time(),
                'usermodified' => $USER->id,
        ];
        typeform_add_typeformsubmission_record($submissiondata);
        $completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);
        rebuild_course_cache($course->id);

        // Trigger event.
        self::add_event('attempt_finished', $cmid);

        return [
            'success' => true,
            'message' => get_string('formcompleted', 'typeform'),
        ];
    }

    /**
     * Returns description of mark_complete result value
     * @return \external_description
     */
    public static function mark_complete_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the request was successful'),
            'message' => new external_value(PARAM_TEXT, 'Message'),
        ]);
    }

    /**
     * Returns description of mark_complete parameters
     * @return external_function_parameters
     */
    public static function add_event_parameters() {
        return new external_function_parameters([
            'eventname' => new external_value(PARAM_RAW, 'Eventname'),
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    /**
     * Mark typeform activity as complete for current user
     *
     * @param string $eventname
     * @param int $cmid Course module ID
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function add_event(string $eventname, int $cmid) {
        global $USER, $PAGE;

        $params = self::validate_parameters(
            self::add_event_parameters(),
            ['cmid' => $cmid, 'eventname' => $eventname]
        );

        if (!in_array($eventname, ['attempt_started', 'attempt_finished'])) {
            return [
                    'success' => false,
                    'message' => get_string('eventnotexists', 'typeform'),
            ];
        }
        if (!$cmid) {
            return [
                    'success' => false,
                    'message' => get_string('cmnotexists', 'typeform'),
            ];
        }
        $cm = get_coursemodule_from_id('typeform', $params['cmid'], 0, false);
        if (!$cm) {
            return [
                'success' => false,
                'message' => get_string('cmnotexists', 'typeform'),
            ];
        }
        $context = \core\context\module::instance($cmid);
        $PAGE->set_context($context);

        if ($eventname == 'attempt_started') {
            $submissiondata = [
                    'typeform' => (int)$cm->instance,
                    'submitted' => 0,
                    'userid' => (int)$USER->id,
                    'timemodified' => time(),
                    'timecreated' => time(),
                    'usermodified' => (int)$USER->id,
            ];
            // Si existe, no es inicio de formulario.
            if (typeform_get_typeformsubmission_record(['typeform' => $cm->instance, 'userid' => $USER->id])) {
                return [
                    'success' => true,
                    'message' => get_string('alreadyexist', 'typeform'),
                ];
            }
            // Creo el registro.
            $submissiondata['id'] = typeform_add_typeformsubmission_record($submissiondata);
            $params = [
                'objectid' => $submissiondata['id'],
                'courseid' => $cm->course,
                'context' => \core\context\module::instance($cm->id),
            ];
            $event = \mod_typeform\event\attempt_started::create($params);
            $event->add_record_snapshot('typeform_submission', (object)$submissiondata);
            $message = get_string('formstarted', 'typeform');
        } else {
            $submissiondata = typeform_get_typeformsubmission_record(
                ['typeform' => $cm->instance, 'userid' => $USER->id],
                MUST_EXIST
            );
            $params = [
                    'objectid' => $submissiondata->id,
                    'courseid' => $cm->course,
                    'context' => \core\context\module::instance($cm->id),
            ];
            $event = \mod_typeform\event\attempt_finished::create($params);
            $event->add_record_snapshot('typeform_submission', (object)$submissiondata);
            $message = get_string('formcompleted', 'typeform');
        }
        $event->trigger();
        return [
            'success' => true,
            'message' => $message,
        ];
    }

    /**
     * Returns description of mark_complete result value
     * @return \external_description
     */
    public static function add_event_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the request was successful'),
            'message' => new external_value(PARAM_TEXT, 'Message'),
        ]);
    }

    /**
     * Returns description of mark_complete parameters
     * @return external_function_parameters
     */
    public static function get_workspace_forms_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_RAW, 'Workspace ID'),
        ]);
    }

    /**
     * Mark typeform activity as complete for current user
     *
     * @param string $workspaceid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function get_workspace_forms(string $workspaceid) {

        $params = self::validate_parameters(
            self::get_workspace_forms_parameters(),
            ['id' => $workspaceid]
        );
        $list = [];
        $allforms = [];
        try {
            // Get all pages for this workspace
            $page = 1;
            $hasmore = true;
            while ($hasmore) {
                $result = typeform_api::get_forms($workspaceid, '', $page, 100);
                if ($result === false) {
                    // Error on this workspace, continue with next
                    break;
                }
                if (!empty($result['forms'])) {
                    $allforms = array_merge($allforms, $result['forms']);
                }
                $hasmore = $result['has_more'];
                $page++;
            }
            foreach ($allforms as $form) {
                $list[] = [
                        'id' => $form['id'],
                        'name' => $form['title'],
                ];
            }
            return [
                    'success' => true,
                    'list' => $list,
            ];
        } catch (moodle_exception) {
            return [
                    'success' => false,
                    'list' => $list,
            ];
        }
    }

    /**
     * Returns description of mark_complete result value
     * @return \external_description
     */
    public static function get_workspace_forms_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the request was successful'),
            'list' => new external_multiple_structure(
                new external_single_structure([
                        'id' => new external_value(PARAM_RAW, 'Typeform ID'),
                        'name' => new external_value(PARAM_RAW, 'Typeform name'),
                    ]),
                '',
                VALUE_OPTIONAL
            ),
        ]);
    }
}
