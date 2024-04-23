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
 * Backup steps.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Backup steps.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_structlabel_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define structure.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // The main table.
        $structlabel = new backup_nested_element('structlabel', ['id'], ['name', 'intro', 'introformat']);
        $structlabel->set_source_table('structlabel', array('id' => backup::VAR_ACTIVITYID));

        // The resources.
        $resource = new backup_nested_element('resource', ['id'], ['url', 'text', 'icon']);
        $resource->set_source_table('structlabel_resources', array('structlabelid' => backup::VAR_ACTIVITYID));
        $resources = new backup_nested_element('resources');
        $resources->add_child($resource);
        $structlabel->add_child($resources);

        // Define file annotations.
        $structlabel->annotate_files('mod_structlabel', 'intro', null);
        $structlabel->annotate_files('mod_structlabel', 'image', null);

        // Wrap in activity structure.
        return $this->prepare_activity_structure($structlabel);
    }
}
