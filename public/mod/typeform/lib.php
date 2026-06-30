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
 * Mandatory public API of typeform module
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in Typeform module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know or string for the module purpose.
 */
function typeform_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_COMMUNICATION;

        default:
            return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function typeform_reset_userdata($data) {
    return [];
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * @return array
 */
function typeform_get_view_actions() {
    return ['view', 'view all'];
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * @return array
 */
function typeform_get_post_actions() {
    return ['submit'];
}

/**
 * Add typeform instance.
 * @param object $data
 * @param object $mform
 * @return int new typeform instance id
 */
function typeform_add_instance($data, $mform) {
    global $CFG, $DB;
    $data->timemodified = time();
    $data->id = $DB->insert_record('typeform', $data);
    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'typeform', $data->id, $completiontimeexpected);

    return $data->id;
}

/**
 * Update typeform instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function typeform_update_instance($data, $mform) {
    global $CFG, $DB;

    $data->timemodified = time();
    $data->id = $data->instance;
    $DB->update_record('typeform', $data);

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'typeform', $data->id, $completiontimeexpected);

    return true;
}

/**
 * Delete typeform instance.
 * @param int $id
 * @return bool true
 */
function typeform_delete_instance($id) {
    global $DB;

    if (!$typeform = $DB->get_record('typeform', ['id' => $id])) {
        return false;
    }

    $cm = get_coursemodule_from_instance('typeform', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'typeform', $id, null);

    $DB->delete_records('typeform', ['id' => $typeform->id]);

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * @param object $coursemodule
 * @return cached_cm_info info
 */
function typeform_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;

    if (
        !$typeform = $DB->get_record(
            'typeform',
            ['id' => $coursemodule->instance],
            'id, name, intro, introformat, typeformid, typeformtitle, completionsubmit'
        )
    ) {
        return null;
    }

    $info = new cached_cm_info();
    $info->name = $typeform->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('typeform', $typeform, $coursemodule->id, false);
    }
    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ((int)$coursemodule->completion === COMPLETION_TRACKING_AUTOMATIC) {
        $info->customdata['customcompletionrules']['completionsubmit'] = $typeform->completionsubmit;
    }

    return $info;
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $typeform        typeform object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function typeform_view($typeform, $course, $cm, $context) {
    // Trigger course_module_viewed event.
    $params = [
        'context' => $context,
        'objectid' => $typeform->id,
    ];

    $event = \mod_typeform\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('typeform', $typeform);
    $event->trigger();

    // Completion is handled via JavaScript callback when form is submitted.
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter  if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 * @since Moodle 3.2
 */
function typeform_check_updates_since(cm_info $cm, $from, $filter = []) {
    $updates = course_check_module_updates_since($cm, $from, ['content'], $filter);
    return $updates;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $userid ID override for calendar events
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function typeform_core_calendar_provide_event_action(
    calendar_event $event,
    \core_calendar\action_factory $factory,
    $userid = 0
) {

    global $USER;
    if (empty($userid)) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['typeform'][$event->instance];

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new \moodle_url('/mod/typeform/view.php', ['id' => $cm->id]),
        1,
        true
    );
}
/**
 * Get completion active rule descriptions
 *
 * @param cm_info|stdClass $cm
 * @return array $descriptions the array of descriptions for the custom rules.
 * @throws coding_exception
 */
function typeform_get_completion_active_rule_descriptions($cm): array {
    // Values will be present in cm_info, and we assume these are up to date.
    if (
        empty($cm->customdata['customcompletionrules'])
        || (int)$cm->completion !== COMPLETION_TRACKING_AUTOMATIC
    ) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionsubmit':
                if (!empty($val)) {
                    $descriptions[] = get_string('completionsubmit', 'typeform');
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}

/**
 * Get completion active rule descriptions
 *
 * @param array $params
 * @return int typeform submission id
 * @throws dml_exception
 */
function typeform_add_typeformsubmission_record(array $params): int {
    global $DB;
    $submission = $DB->get_record('typeform_submission', ['typeform' => $params['typeform'], 'userid' => $params['userid']]);
    if (!$submission) {
        return $DB->insert_record('typeform_submission', (object)$params);
    } else {
        $params['id'] = $submission->id;
        return $DB->update_record('typeform_submission', (object)$params);
    }
}

/**
 * typeform_get_typeformsubmission_record
 *
 * @param array $params
 * @param int $strictnes
 * @return false|stdClass
 * @throws dml_exception
 */
function typeform_get_typeformsubmission_record(array $params, int $strictnes = IGNORE_MISSING): false|stdClass {
    global $DB;
    return $DB->get_record(
        'typeform_submission',
        ['typeform' => $params['typeform'], 'userid' => $params['userid']],
        '*',
        $strictnes
    );
}
