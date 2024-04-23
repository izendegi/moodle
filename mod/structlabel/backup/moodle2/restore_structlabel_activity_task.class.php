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
 * Restore task.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/structlabel/backup/moodle2/restore_structlabel_stepslib.php');

/**
 * Restore task.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_structlabel_activity_task extends restore_activity_task {

    /**
     * Define settings.
     */
    protected function define_my_settings() {
    }

    /**
     * Define restore steps.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_structlabel_activity_structure_step('structlabel_structure', 'structlabel.xml'));
    }

    /**
     * Define the contents in the activity that must be decoded.
     *
     * @return array The contents.
     */
    static public function define_decode_contents() {
        return [
            new restore_decode_content('structlabel', ['intro'], 'structlabel')
        ];
    }

    /**
     * Define the decoding rules.
     *
     * @return array The rules.
     */
    static public function define_decode_rules() {
        return [];
    }

    /**
     * Define the restore log rules.
     *
     * @return array The rules.
     */
    static public function define_restore_log_rules() {
        return [];
    }

    /**
     * Define the restore log rules.
     *
     * @return array The rules.
     */
    static public function define_restore_log_rules_for_course() {
        return [];
    }
}
