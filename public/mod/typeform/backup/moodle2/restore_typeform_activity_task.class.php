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
 * Defines restore_typeform_activity_task class
 *
 * @package     mod_typeform
 * @copyright   2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/typeform/backup/moodle2/restore_typeform_stepslib.php');

/**
 * Typeform restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_typeform_activity_task extends restore_activity_task {
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Typeform only has one structure step
        $this->add_step(new restore_typeform_activity_structure_step('typeform_structure', 'typeform.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('typeform', ['intro'], 'typeform');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        $rules = [];

        $rules[] = new restore_decode_rule('TYPEFORMINDEX', '/mod/typeform/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('TYPEFORMVIEWBYID', '/mod/typeform/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('TYPEFORMVIEWBYU', '/mod/typeform/view.php?u=$1', 'typeform');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * typeform logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('typeform', 'add', 'view.php?id={course_module}', '{typeform}');
        $rules[] = new restore_log_rule('typeform', 'update', 'view.php?id={course_module}', '{typeform}');
        $rules[] = new restore_log_rule('typeform', 'view', 'view.php?id={course_module}', '{typeform}');

        return $rules;
    }
}
