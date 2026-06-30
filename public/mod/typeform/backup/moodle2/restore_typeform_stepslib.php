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
 * Defines restore_typeform_activity_structure_step class
 *
 * @package     mod_typeform
 * @copyright   2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one typeform activity
 */
class restore_typeform_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines the restore structure for the activity, including paths to XML elements.
     *
     * This method specifies the structure required to restore the activity data,
     * determining which XML elements are processed and restored.
     *
     * The structure is conditionally adjusted based on whether user information
     * is included in the restore process.
     *
     * @return array The processed restore paths wrapped into the standard activity structure.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');
        $paths = [];
        $paths[] = new restore_path_element(
            'typeform',
            '/activity/typeform'
        );
        if ($userinfo) {
            $paths[] = new restore_path_element(
                'typeform_submission',
                '/activity/typeform/submissions/typeform_submission'
            );
        }
        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * process_typeform
     * @param $data
     * @return void
     * @throws base_step_exception
     * @throws dml_exception
     */
    protected function process_typeform($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the typeform record.
        $newitemid = $DB->insert_record('typeform', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Processes and restores a Typeform submission during the restore process.
     *
     * This method handles the restoration of a Typeform submission by reconstructing
     * the submission data, mapping user and typeform IDs, applying necessary date offsets,
     * and inserting the record into the database.
     *
     * The processing is conditional on the inclusion of user information in the restore settings.
     *
     * @param array $data Associative array containing the submission data to restore.
     * @return void
     */
    protected function process_typeform_submission($data) {
        global $DB;

        if (!$this->get_setting_value('userinfo')) {
            return;
        }
        $data = (object)$data;
        $data->typeform = $this->get_new_parentid('typeform');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the typeform record.
        $DB->insert_record('typeform_submission', $data);
    }

    /**
     * Finalizes the execution process by handling related files for the activity.
     *
     * This method is responsible for adding related files associated with the activity.
     * The files are managed internally and do not require matching by item name.
     *
     * @return void No return value as the method performs file handling operations.
     */
    protected function after_execute() {
        // Add typeform related files, no need to match by itemname (just internally handled).
        $this->add_related_files('mod_typeform', 'intro', null);
    }
}
