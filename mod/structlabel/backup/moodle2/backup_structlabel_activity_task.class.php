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
 * Backup task.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/structlabel/backup/moodle2/backup_structlabel_stepslib.php');

/**
 * Backup task.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_structlabel_activity_task extends backup_activity_task {

    /**
     * Settings.
     */
    protected function define_my_settings() {
    }

    /**
     * Steps.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_structlabel_activity_structure_step('structlabel_structure', 'structlabel.xml'));
    }

    /**
     * Content encoding.
     *
     * @param string $content HTML.
     * @return string HTML.
     */
    static public function encode_content_links($content) {
        return $content;
    }
}
