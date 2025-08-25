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
namespace report_fundae;
use OpenSpout\Common\Entity\Cell;
use coding_exception;
use core_user;
use dml_exception;
use moodle_exception;
use report_fundae\reportbuilder\local\api;

/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

class coursereportapi {
    /**
     * @return string[]
     * @throws coding_exception
     */
    public static function get_headers() : array {
        global $CFG;
        if (file_exists($CFG->dirroot . '/mod/zoom/locallib.php')) {
            return [
                'lastname' => get_string('lastname'),
                'firstname' => get_string('firstname'),
                'email' => get_string('email'),
                'coursename' => get_string('course'),
                'enroldate' => get_string('enroltimecreated', 'enrol'),
                'firstaccess' => get_string('firstaccess'),
                'lastaccess' => get_string('lastaccess'),
                'coursestartdate' => get_string('startdate'),
                'courseenddate' => get_string('enddate'),
                'progresspercent' => get_string('progress', 'completion'),
                'dedicationtime' => get_string('timeoncourse', 'report_fundae'),
                'totalactivitiestimes' => get_string('timeactivities', 'report_fundae'),
                'totalscormtime' => get_string('timescorms', 'report_fundae'),
                'totalzoomtime' => get_string('timezoom', 'report_fundae'),
                'daysconnectedcount' => get_string('daysonline', 'report_fundae'),
                'connectionratio' => get_string('ratioonline', 'report_fundae'),
                'messagestostudentscount' => get_string('messagestostudents', 'report_fundae'),
                'messagestoteacherscount' => get_string('messagestoteachers', 'report_fundae')
            ];
        }

        return [
            'lastname' => get_string('lastname'),
            'firstname' => get_string('firstname'),
            'email' => get_string('email'),
            'coursename' => get_string('course'),
            'enroldate' => get_string('enroltimecreated', 'enrol'),
            'firstaccess' => get_string('firstaccess'),
            'lastaccess' => get_string('lastaccess'),
            'coursestartdate' => get_string('startdate'),
            'courseenddate' => get_string('enddate'),
            'progresspercent' => get_string('progress', 'completion'),
            'dedicationtime' => get_string('timeoncourse', 'report_fundae'),
            'totalactivitiestimes' => get_string('timeactivities', 'report_fundae'),
            'totalscormtime' => get_string('timescorms', 'report_fundae'),
            'daysconnectedcount' => get_string('daysonline', 'report_fundae'),
            'connectionratio' => get_string('ratioonline', 'report_fundae'),
            'messagestostudentscount' => get_string('messagestostudents', 'report_fundae'),
            'messagestoteacherscount' => get_string('messagestoteachers', 'report_fundae')
        ];
    }

    /**
     * @param int $courseid
     * @param bool $cellformat
     * @param bool $isstudent
     * @param int $limitfrom
     * @param int $pagesize
     * @param int $reportuserid
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_data(int $courseid, bool $cellformat = true, bool $isstudent = false, int $limitfrom = 0, int $pagesize = 0, int $reportuserid = 0) : array {
        global $USER;
        $data = [];
        $course = get_course($courseid);
        $userenrolments = array_values(enrol_get_course_users($courseid, true));
        if (($limitfrom !== 0 || $pagesize !== 0) && $isstudent === false) {
            $userenrolments = array_slice($userenrolments, $limitfrom, $pagesize);
        }
        foreach ($userenrolments as $userenrolment) {
            if ($isstudent === false && $reportuserid !== 0 && $reportuserid !== (int)$userenrolment->id) {
                continue;
            }
            $userid = $userenrolment->id;
            if ($isstudent === true && (int)$USER->id !== (int)$userid) {
                continue;
            }
            $user = core_user::get_user($userid);
            $api = new api($userid, $courseid);
            $results = $api->get_cached_object();
            $userdata = self::data_text_format($user, $course, $userenrolment, $results);
            if ($cellformat) {
                $userdata = self::data_cell_format($user, $course, $userenrolment, $results);
            }
            $data[] = $userdata;
        }
        return $data;
    }

    /**
     * @param $user
     * @param $course
     * @param $userenrolment
     * @param $results
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function data_text_format($user, $course, $userenrolment, $results) {
        $userdata = [];
        $userdata['userid'] = $user->id;
        $userdata['lastname'] = $user->lastname;
        $userdata['firstname'] = $user->firstname;
        $userdata['email'] = $user->email;
        $userdata['coursename'] = $course->fullname;
        $userdata['enroldate'] = $userenrolment->uetimecreated === 0 ? '-' : userdate($userenrolment->uetimecreated, get_string('strftimedatetimeshort'));
        $userdata['firstaccess'] = $results->{'firstaccess'};
        $userdata['lastaccess'] = $results->{'lastaccess'};
        $userdata['coursestartdate'] = $results->{'coursestartdate'};
        $userdata['courseenddate'] = $results->{'courseenddate'};
        $userdata['progresspercent'] = $results->{'percentage'};
        $userdata['dedicationtime'] = $results->{'dedicationtime'};
        $userdata['totalactivitiestimes'] = $results->{'totalactivitiestimes'};
        $userdata['totalscormtime'] = $results->{'totalscormtime'};
        $userdata['totalzoomtime'] = $results->{'totalzoomtime'};
        if ((int)get_config('report_fundae', 'bbbreports') === 1) {
            $userdata['totalbbbtime'] = $results->{'totalbbbtime'};
        }
        $userdata['daysconnectedcount'] = $results->{'daysconnectedcount'};
        $userdata['connectionratio'] = $results->{'connectionratio'};
        $userdata['messagestostudentscount'] = $results->{'messagestostudentscount'};
        $userdata['messagestoteacherscount'] = $results->{'messagestoteacherscount'};

        return $userdata;
    }

    /**
     * @param $user
     * @param $course
     * @param $userenrolment
     * @param $results
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function data_cell_format($user, $course, $userenrolment, $results) {
        $userdata = [];
        $userdata[] = Cell::fromValue($user->lastname);
        $userdata[] = Cell::fromValue($user->firstname);
        $userdata[] = Cell::fromValue($user->email);
        $userdata[] = Cell::fromValue($course->fullname);
        $userdata[] = Cell::fromValue(userdate($userenrolment->uetimestart, get_string('strftimedatetimeshort')));
        $userdata[] = Cell::fromValue($results->{'firstaccess'});
        $userdata[] = Cell::fromValue($results->{'lastaccess'});
        $userdata[] = Cell::fromValue($results->{'coursestartdate'});
        $userdata[] = Cell::fromValue($results->{'courseenddate'});
        $userdata[] = Cell::fromValue($results->{'percentage'});
        $userdata[] = Cell::fromValue($results->{'dedicationtime'});
        $userdata[] = Cell::fromValue($results->{'totalactivitiestimes'});
        $userdata[] = Cell::fromValue($results->{'totalscormtime'});
        $userdata[] = Cell::fromValue($results->{'totalzoomtime'});
        if ((int)get_config('report_fundae', 'bbbreports') === 1) {
            $userdata[] = Cell::fromValue($results->{'totalbbbtime'});
        }
        $userdata[] = Cell::fromValue($results->{'daysconnectedcount'});
        $userdata[] = Cell::fromValue($results->{'connectionratio'});
        $userdata[] = Cell::fromValue($results->{'messagestostudentscount'});
        $userdata[] = Cell::fromValue($results->{'messagestoteacherscount'});
        return $userdata;
    }
}
