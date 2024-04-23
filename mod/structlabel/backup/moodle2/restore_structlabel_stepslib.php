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
 * Restore steps.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restore steps.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_structlabel_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define structure.
     *
     * @return restore_path_element
     */
    protected function define_structure() {

        $paths = [
            new restore_path_element('structlabel', '/activity/structlabel'),
            new restore_path_element('resource', '/activity/structlabel/resources/resource'),
        ];

        // Wrap in structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process activity.
     *
     * @param array $data The data.
     * @return void
     */
    protected function process_structlabel($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record('structlabel', $data);

        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process resources.
     *
     * @param array $data The data.
     * @return void
     */
    protected function process_resource($data) {
        global $DB;
        $data = (object) $data;
        $data->structlabelid = $this->get_new_parentid('structlabel');
        $newitemid = $DB->insert_record('structlabel_resources', $data);
    }

    /**
     * After execute.
     *
     * @return void
     */
    protected function after_execute() {
        $this->add_related_files('mod_structlabel', 'intro', null);
        $this->add_related_files('mod_structlabel', 'image', null);
    }

}
