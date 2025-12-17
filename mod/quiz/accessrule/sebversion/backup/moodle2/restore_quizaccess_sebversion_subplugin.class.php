<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Restore helper functions for the quizaccess_sebversion plugin.
 *
 * @package    quizaccess_sebversion
 * @category   backup
 * @author     Philipp Imhof
 * @copyright  2025, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/restore_mod_quiz_access_subplugin.class.php');

/**
 * Restore helper functions for the quizaccess_sebversion plugin.
 *
 * @copyright 2025, Philipp Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_quizaccess_sebversion_subplugin extends restore_mod_quiz_access_subplugin {
    #[\Override]
    protected function define_quiz_subplugin_structure() {
        return [
            new restore_path_element(
                'quizaccess_sebversion',
                $this->get_pathfor('/quizaccess_sebversion'),
            ),
        ];
    }

    /**
     * Process the restored data for the quizaccess_sebversion table.
     *
     * @param stdClass $data Data for quizaccess_sebversion retrieved from backup xml.
     */
    public function process_quizaccess_sebversion($data) {
        global $DB;

        // Update quizid with new reference.
        $data = (object) $data;
        $data->quizid = $this->get_new_parentid('quiz');

        $DB->insert_record('quizaccess_sebversion', $data);
    }
}
