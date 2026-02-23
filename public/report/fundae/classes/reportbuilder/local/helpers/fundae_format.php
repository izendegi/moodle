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

namespace report_fundae\reportbuilder\local\helpers;

use coding_exception;
use completion_criteria_completion;
use completion_info;
use context_course;
use dml_exception;
use html_writer;
use moodle_exception;
use moodle_url;
use pix_icon;
use stdClass;
use report_fundae\reportbuilder\local\api;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */
class fundae_format {

    /**
     * @param stdClass $row
     * @return bool
     */
    private static function arevaluesvalid(stdClass $row) : bool {

        if (isset($row->{'courseid'})) {
            $courseid = $row->{'courseid'};
        }
        if (isset($row->{'userid'})) {
            $userid = $row->{'userid'};
        }

        if (is_null($courseid) || is_null($userid)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws dml_exception
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function firstaccess(string $value, stdClass $row) : string {

        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'firstaccess'};
    }

    /**
     *
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws dml_exception
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function timeoncourse(string $value, stdClass $row) : string {

        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'dedicationtime'};
    }

    /**
     *
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function timeactivities(string $value, stdClass $row) : string {
        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'totalactivitiestimes'};
    }

    /**
     *
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function timescorms(string $value, stdClass $row) : string {
        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'totalscormtime'};
    }

    /**
     *
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function timezoom(string $value, stdClass $row) : string {
        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'totalzoomtime'};
    }

    /**
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function timebbb(string $value, stdClass $row) : string {
        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'totalbbbtime'};
    }

    /**
     *
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function numberofsessions(string $value, stdClass $row) : string {
        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'sessionscount'};
    }

    /**
     *
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function daysonline(string $value, stdClass $row) : string {

        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'daysconnectedcount'};
    }

    /**
     *
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function ratioonline(string $value, stdClass $row) : string {
        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'connectionratio'};
    }

    /**
     *
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function messagestostudents(string $value, stdClass $row) : string {
        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'messagestostudentscount'};
    }

    /**
     *
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function messagestoteachers(string $value, stdClass $row) : string {
        if (!self::arevaluesvalid($row)) {
            return '';
        }
        $courseid = $row->{'courseid'};
        $userid = $row->{'userid'};
        $api = new api($userid, $courseid);
        return $api->get_cached_object()->{'messagestoteacherscount'};
    }

    /**
     * @param string $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function details(string $value, stdClass $row): string {
        global $OUTPUT, $USER;
        $alt = get_string('details', 'report_fundae');
        $url = new moodle_url('/report/fundae/userdetails.php', [
            'courseid' => $row->courseid,
            'userid' => $row->userid,
        ]);
        $html = html_writer::start_div('d-inline-flex');
        $html .= html_writer::start_div('btn btn-primary mr-2');
        $html .= $OUTPUT->action_icon(
            $url,
            new pix_icon('t/viewdetails', $alt, 'core', ['class' => 'text-light mr-0']),
            null,
            ['target' => 'blank']
        );
        $html .= html_writer::end_div();
        $coursecontext = context_course::instance($row->courseid);
        if (has_capability('moodle/user:viewdetails', $coursecontext, $USER->id)) {
            $alt = get_string('profile');
            $url = new moodle_url('/user/view.php', [
                'id' => $row->userid,
                'course' => $row->courseid,
            ]);
            $html .= html_writer::start_div('btn btn-primary mr-2');
            $html .= $OUTPUT->action_icon(
                $url,
                new pix_icon('i/user', $alt, 'core', ['class' => 'text-light mr-0']),
                null,
                ['target' => 'blank']
            );
            $html .= html_writer::end_div();
        }
        if (has_capability('moodle/grade:viewall', $coursecontext, $USER->id)) {
            $alt = get_string('urlgradebook', 'report_fundae');
            $url = new moodle_url('/course/user.php', [
                'mode' => 'grade',
                'id' => $row->courseid,
                'user' => $row->userid
            ]);
            $html .= html_writer::start_div('btn btn-primary mr-2');
            $html .= $OUTPUT->action_icon(
                $url,
                new pix_icon('i/grades', $alt, 'core', ['class' => 'text-light mr-0']),
                null,
                ['target' => '_blank']
            );
            $html .= html_writer::end_div();
        }
        $html .= html_writer::end_div();
        return $html;
    }

    /**
     *
     * @param mixed $value
     * @return string
     * @throws coding_exception
     */
    public static function checkbox_as_text($value) {
        return $value ? get_string('yes') : get_string('no');
    }

    /**
     *
     * @param string|null $value
     * @param stdClass $row
     * @param bool $percent
     * @return string
     */
    public static function completion_progress(?string $value, stdClass $row, ?bool $percent = false) : string {
        global $CFG;
        require_once($CFG->libdir . '/completionlib.php');
        $completion = new completion_info((object)['id' => $row->courseid, 'enablecompletion' => $row->enablecompletion]);

        // Bail out early if completion not enabled, or not tracking user.
        if (!$completion->is_enabled() || !$completion->is_tracked_user($row->userid)) {
            return '';
        }

        /** @var completion_criteria_completion[] $completions */
        $completions = $completion->get_completions($row->userid);
        if (0 === ($totalcount = count($completions))) {
            return '';
        }

        // Filter criteria to those completed by user.
        $completed = array_filter($completions, static function(completion_criteria_completion $completion): bool {
            return $completion->is_complete();
        });

        $completedcount = count($completed);

        return $percent ? self::percent(100 * $completedcount / $totalcount) : sprintf('%d / %d', $completedcount, $totalcount);
    }

    /**
     * Formats as number and adds a '%' in the end
     *
     * @param mixed $value
     * @param stdClass|null $row
     * @return string
     */
    public static function percent($value, stdClass $row = null): string {
        if (is_numeric($value)) {
            return sprintf("%.2f", $value) . '%';
        }
        return '';
    }

    /**
     *
     * @param string $value
     * @param stdClass $row
     * @param null $format
     * @return string
     * @throws coding_exception
     */
    public static function userdate($value, stdClass $row, $format = null) {
        if (!$format) {
            $format = get_string('strftimedatefullshort');
        }
        return $value ? userdate($value, $format) : '';
    }

    /**
     *
     * @param mixed $value
     * @param stdClass $row
     * @return string
     * @throws coding_exception
     */
    public static function days($value, stdClass $row) : string {
        $days = floor($value);
        return ($days > 0) ? (string)$days : get_string('lessthanaday', 'report_fundae');
    }
}
