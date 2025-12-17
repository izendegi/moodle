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
 * Backup helper functions for the quizaccess_sebversion plugin.
 *
 * @package    quizaccess_sebversion
 * @category   backup
 * @author     Philipp Imhof
 * @copyright  2025, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/backup_mod_quiz_access_subplugin.class.php');

/**
 * Backup helper functions for the quizaccess_sebversion plugin.
 *
 * @copyright 2025, Philipp Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_quizaccess_sebversion_subplugin extends backup_mod_quiz_access_subplugin {
    #[\Override]
    protected function define_quiz_subplugin_structure() {
        parent::define_quiz_subplugin_structure();
        $quizid = backup::VAR_ACTIVITYID;

        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Create element for our setting and set the source table from the DB.
        $subpluginsettings = new backup_nested_element('quizaccess_sebversion', null, ['enforceversion']);
        $subpluginsettings->set_source_table('quizaccess_sebversion', ['quizid' => $quizid]);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginsettings);

        return $subplugin;
    }
}
