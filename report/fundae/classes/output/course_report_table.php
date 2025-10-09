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
namespace report_fundae\output;

defined('MOODLE_INTERNAL') || die();
/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

global $CFG;
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/report/fundae/locallib.php');
require_once($CFG->dirroot . '/user/lib.php');

use coding_exception;
use context_course;
use dml_exception;
use help_icon;
use html_writer;
use moodle_exception;
use moodle_url;
use pix_icon;
use renderable;
use report_fundae\coursereportapi;
use stdClass;
use table_sql;

defined('MOODLE_INTERNAL') || die;
/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

class course_report_table extends table_sql implements renderable {

    protected int $courseid;
    protected int $page;
    protected int $perpage;
    protected bool $isstudent;
    protected int $userid;

    /**
     * course_report_table constructor.
     *
     * @param int $courseid The course ID
     * @param int $perpage
     * @param int $page
     * @param string $download
     * @param bool $isstudent
     * @param int $userid
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function __construct(int $courseid, int $perpage, int $page, string $download = '', bool $isstudent = false, int $userid = 0) {
        parent::__construct('report_fundae');
        if (!$courseid) {
            throw new coding_exception('Missing parameters');
        }
        $this->courseid = $courseid;
        $this->page = $page;
        $this->perpage = $perpage;
        $this->isstudent = $isstudent;
        $this->userid = $userid;
        $columnheaders = self::get_table_headers();
        $this->define_columns(array_keys($columnheaders));
        $this->define_headers(array_values($columnheaders));
        $this->column_class = array_combine(array_keys($columnheaders),
            array_fill(0, count($columnheaders), 'header c6'));
        $this->column_nosort = array_keys($columnheaders);
        $threshold = (int)get_config('report_fundae', 'sessionthreshold');
        $helpicons = $this->get_table_help_icons($threshold);
        $this->define_help_for_headers($helpicons);
        $this->is_downloadable(true);
        $this->pageable(true);
        $this->define_baseurl(new moodle_url('/report/fundae/coursereport.php', [
            'courseid' => $this->courseid,
            'perpage' => $perpage,
            'page' => $page,
        ]));
        $course = get_course($courseid);
        $filename = get_string('details', 'report_fundae');
        $filename .= " - $course->fullname";
        $this->is_downloading($download, $filename);
        if ($this->is_downloading()) {
            $this->start_output();
        }
    }

    /**
     *
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @throws moodle_exception
     */
    final public function query_db($pagesize, $useinitialsbar = true): void {
        $limitfrom = $this->currpage * $pagesize;
        if ($this->is_downloading()) {
            $data = coursereportapi::get_data($this->courseid, false, $this->isstudent);
        } else {
            $data = coursereportapi::get_data($this->courseid, false, $this->isstudent, $limitfrom, $pagesize, $this->userid);
        }
        $this->rawdata = $data;
        $total = count(enrol_get_course_users($this->courseid, true));
        if ($this->isstudent === false) {
            $this->pagesize($pagesize, $total);
        }
        $this->initialbars(false);
    }

    /**
     * @return array
     * @throws coding_exception
     */
    public static function get_table_headers(): array {
        $headers = coursereportapi::get_headers();
        if ((int)get_config('report_fundae', 'bbbreports') === 1) {
            $headers = array_slice($headers, 0, 12) + ['totalbbbtime' => get_string('timebbb', 'report_fundae')] + $headers;
        }
        $headers['actions'] = get_string('actions', 'report_fundae');
        return $headers;
    }

    /**
     * @param stdClass $data
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function col_actions(stdClass $data): string {
        global $OUTPUT, $USER;
        $icons = html_writer::start_div('d-flex justify-content-center');
        $alt = get_string('activitiesreport', 'report_fundae');
        $url = new moodle_url("/report/fundae/userdetails.php", [
            'courseid' => $this->courseid,
            'userid' => $data->userid,
        ]);
        $icons .= $OUTPUT->action_icon($url, new pix_icon('t/viewdetails', $alt, 'core'), null, ['target' => '_blank', 'class' => 'd-flex']);
        $coursecontext = context_course::instance($this->courseid);
        if (has_capability('moodle/user:viewdetails', $coursecontext, $USER->id)) {
            $alt = get_string('viewuser', 'report_fundae');
            $url = new moodle_url('/user/view.php', ['id' => $data->userid, 'course' => $this->courseid]);
            $icons .= $OUTPUT->action_icon($url, new pix_icon('i/user', $alt, 'core'), null, ['target' => '_blank', 'class' => 'd-flex']);
        }
        if (has_capability('moodle/grade:viewall', $coursecontext, $USER->id)) {
            $alt = get_string('urlgradebook', 'report_fundae');
            $url = new moodle_url('/course/user.php', ['mode' => 'grade', 'id' => $this->courseid, 'user' => $data->userid]);
            $icons .= $OUTPUT->action_icon($url, new pix_icon('i/grades', $alt, 'core'), null, ['target' => '_blank', 'class' => 'd-flex']);
        }
        $icons .= html_writer::end_div();

        return $icons;
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_lastname(stdClass $data): string {
        return $data->{'lastname'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_firstname(stdClass $data): string {
        return $data->{'firstname'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_email(stdClass $data): string {
        return $data->{'email'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_coursename(stdClass $data): string {
        return $data->{'coursename'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_enroldate(stdClass $data): string {
        return $data->{'enroldate'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_firstaccess(stdClass $data): string {
        return $data->{'firstaccess'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_lastaccess(stdClass $data): string {
        return $data->{'lastaccess'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_coursestartdate(stdClass $data): string {
        return $data->{'coursestartdate'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_courseenddate(stdClass $data): string {
        return $data->{'courseenddate'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_percentage(stdClass $data): string {
        return $data->{'percentage'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_dedicationtime(stdClass $data): string {
        return $data->{'dedicationtime'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_totalactivitiestimes(stdClass $data): string {
        return $data->{'totalactivitiestimes'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_totalscormtime(stdClass $data): string {
        return $data->{'totalscormtime'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_totalzoomtime(stdClass $data): string {
        return $data->{'totalzoomtime'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_totalbbbtime(stdClass $data): string {
        return $data->{'totalbbbtime'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_daysconnectedcount(stdClass $data): string {
        return $data->{'daysconnectedcount'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_connectionratio(stdClass $data): string {
        return $data->{'connectionratio'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_messagestostudentscount(stdClass $data): string {
        return $data->{'messagestostudentscount'};
    }

    /**
     * @param stdClass $data
     * @return string
     */
    public function col_messagestoteacherscount(stdClass $data): string {
        return $data->{'messagestoteacherscount'};
    }

    /**
     * @param int $threshold
     * @return array
     */
    protected function get_table_help_icons(int $threshold): array {
        global $CFG;
        if (file_exists($CFG->dirroot . '/mod/zoom/locallib.php')) {
            return [
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                new help_icon('coursecompletion', 'report_fundae'),
                new help_icon('dedicationtime', 'report_fundae', $threshold),
                new help_icon('totalactivitiestimes', 'report_fundae'),
                new help_icon('totalscormtime', 'report_fundae'),
                null,
                null,
                new help_icon('connectionratio', 'report_fundae'),
                null,
                null,
                null,
            ];
        }
        return [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            new help_icon('coursecompletion', 'report_fundae'),
            new help_icon('dedicationtime', 'report_fundae', $threshold),
            new help_icon('totalactivitiestimes', 'report_fundae'),
            new help_icon('totalscormtime', 'report_fundae'),
            null,
            new help_icon('connectionratio', 'report_fundae'),
            null,
            null,
            null,
        ];
    }
}
