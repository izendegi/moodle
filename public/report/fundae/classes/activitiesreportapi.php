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



namespace report_fundae;
require_once $CFG->dirroot . '/report/fundae/locallib.php';

use coding_exception;
use dml_exception;
use moodle_exception;
use OpenSpout\Common\Entity\Cell;

class activitiesreportapi {
    /**
     * @return array
     * @throws coding_exception
     */
    public static function get_headers() : array {
        return [
            'name' => get_string('activityname', 'report_fundae'),
            'timeelapsed' => get_string('dedicationtime', 'report_fundae'),
            'hits' => get_string('hits', 'report_fundae'),
            'firstaccess' => get_string('activityfirstaccess', 'report_fundae'),
            'lastaccess' => get_string('activitylastaccess', 'report_fundae'),
        ];
    }

    /**
     * @param $courseid
     * @param $userid
     * @return array
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_data($courseid, $userid) : array {
        $data = [];
        $activities = get_activities_stats($courseid, $userid);
        foreach ($activities as $activity) {
            $firstaccess = '-';
            if(isset($activity->{'firstaccess'})) {
                $firstaccess = userdate($activity->{'firstaccess'}, get_string('strftimedatetimeshort'));
            }
            $lastaccess = '-';
            if(isset($activity->{'firstaccess'})) {
                $lastaccess = userdate($activity->{'lastaccess'}, get_string('strftimedatetimeshort'));
            }
            $timeelapsed = 0;
            if(isset($activity->{'timeelapsed'})) {
                $timeelapsed = format_report_time($activity->{'timeelapsed'});
            }
            $hits = isset($activity->{'hits'}) ? $activity->{'hits'} : 0;
            $name = isset($activity->{'name'}) ? $activity->{'name'} : '';

            $userdata = [];
            $userdata[] = Cell::fromValue($name);
            $userdata[] = Cell::fromValue($timeelapsed);
            $userdata[] = Cell::fromValue($hits);
            $userdata[] = Cell::fromValue($firstaccess);
            $userdata[] = Cell::fromValue($lastaccess);
            $data[] = $userdata;
        }

        return $data;
    }
}
