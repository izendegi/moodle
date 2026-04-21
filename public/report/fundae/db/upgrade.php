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
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param int $oldversion
 * @return true
 * @throws dml_exception
 */
function xmldb_report_fundae_upgrade(int $oldversion = 0): bool {
    global $CFG, $DB;
    $moodle43 = str_contains($CFG->release, '4.3');
    if ($moodle43 && $oldversion < 2024032000) {
        $reportsfundae = $DB->get_records('reportbuilder_report', ['source' => 'report_fundae\reportbuilder\datasource\report_fundae'], '', 'id');
        foreach ($reportsfundae as $report) {
            $columns = $DB->get_records('reportbuilder_column', ['reportid' => $report->id]);
            foreach ($columns as $column) {
                switch ($column->uniqueidentifier) {
                    case 'course_completion:progresspercent':
                        $column->uniqueidentifier = 'fundae_course_completion:progresspercent';
                        break;
                    case 'course_completion:completed':
                        $column->uniqueidentifier = 'fundae_course_completion:completed';
                        break;
                    case 'user_time:timeoncourse':
                        $column->uniqueidentifier = 'fundae_user_time:timeoncourse';
                        break;
                    case 'user_time:timeactivities':
                        $column->uniqueidentifier = 'fundae_user_time:timeactivities';
                        break;
                    case 'user_time:timescorms':
                        $column->uniqueidentifier = 'fundae_user_time:timescorms';
                        break;
                    case 'user_time:timezoom':
                        $column->uniqueidentifier = 'fundae_user_time:timezoom';
                        break;
                    case 'user_time:numberofsessions':
                        $column->uniqueidentifier = 'fundae_user_time:numberofsessions';
                        break;
                    case 'user_time:daysonline':
                        $column->uniqueidentifier = 'fundae_user_time:daysonline';
                        break;
                    case 'user_time:ratioonline':
                        $column->uniqueidentifier = 'fundae_user_time:ratioonline';
                        break;
                    case 'user_messaging:messagestostudents':
                        $column->uniqueidentifier = 'fundae_user_messaging:messagestostudents';
                        break;
                    case 'user_messaging:messagestoteachers':
                        $column->uniqueidentifier = 'fundae_user_messaging:messagestoteachers';
                        break;
                    case 'user_access:firstaccess':
                        $column->uniqueidentifier = 'fundae_user_access:firstaccess';
                        break;
                    case 'user_access:lastaccess':
                        $column->uniqueidentifier = 'fundae_user_access:lastaccess';
                        break;
                    case 'user_details:details':
                        $column->uniqueidentifier = 'fundae_user_details:details';
                        break;
                    default:
                        break;
                }
                $DB->update_record('reportbuilder_column', $column);
            }
        }
    }
    return true;
}