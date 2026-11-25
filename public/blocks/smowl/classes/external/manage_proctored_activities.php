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

namespace block_smowl\external;
defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/externallib.php");

/**
 * Externallib File.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_proctored_activities extends \external_api {
    /**
     * Describes the parameters.
     * @return manage_proctored_activities_parameters
     */
    public static function manage_proctored_activities_parameters() {
        return new \external_function_parameters(
            [
                'courseid' => new \external_value(PARAM_INT, 'ID of the course'),
                'updateactivities' => new \external_multiple_structure(
                    new \external_single_structure(
                        [
                            'module' => new \external_value(PARAM_TEXT, 'name'),
                            'instanceid' => new \external_value(PARAM_INT, 'ID of instance'),
                            'coursemodule' => new \external_value(PARAM_INT, 'ID of coursemodule'),
                            'active' => new \external_value(PARAM_BOOL, 'activation'),
                        ], 'Activities data', VALUE_OPTIONAL
                    )
                ),
            ]
        );
    }

    /**
     * Execute the service.
     * @param int $courseid
     * @param array $updateactivities
     * @return string
     */
    public static function manage_proctored_activities($courseid, $updateactivities) {
        global $CFG;

        $params = [
            'courseid' => $courseid,
            'updateactivities' => $updateactivities,
        ];
        $params = self::validate_parameters(self::manage_proctored_activities_parameters(), $params);

        require_once($CFG->dirroot . '/blocks/smowl/lib.php');

        $context = \context_course::instance($courseid);
        self::validate_context($context);
        require_capability('block/smowl:manageactivities', $context);

        if (empty($updateactivities)) {
            return false;
        }

        $activities = [];
        foreach ($updateactivities as $module) {
            if (!isset($module['module']) || !isset($module['coursemodule']) || !isset($module['active'])) {
                continue;
            }
            $inputname = 'mod-'.$module['module'].'-'.$module['coursemodule'];
            $activities[$inputname] = $module['active'];
        }

        // Activate and check course block.
        $cbi = block_smowl_check_course_block($courseid);
        if (!isset($cbi->id)) {
            $cbi = block_smowl_create_course_instance($courseid);
        }

        $blockinstances = block_smowl_get_instances($courseid);
        block_smowl_check_instances($activities, $blockinstances, false, false);

        // TODO Improve the return with information about the data processed.
        return true;
    }

    /**
     * Describes the return structure of the service.
     * @return external_value
     */
    public static function manage_proctored_activities_returns() {
        // TODO Improve the return with information about the data processed.
        return new \external_value(PARAM_BOOL, 'Status');
    }
}
