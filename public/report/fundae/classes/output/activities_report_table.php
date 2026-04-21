<?php

namespace report_fundae\output;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/report/fundae/locallib.php');
require_once($CFG->dirroot . '/user/lib.php');

use coding_exception;
use core_user;
use moodle_exception;
use moodle_url;
use renderable;
use stdClass;
use table_sql;

defined('MOODLE_INTERNAL') || die;

class activities_report_table extends table_sql implements renderable {

    protected $courseid;
    protected $userid;

    /**
     * course_report_table constructor.
     *
     * @param int $courseid The course ID
     * @param int $userid
     * @param string $download
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function __construct($courseid, $userid, $download = '') {
        parent::__construct('report_fundae');
        if (!$courseid || !$userid) {
            throw new coding_exception('Missing parameters');
        }
        $this->courseid = $courseid;
        $this->userid = $userid;
        $columnheaders = self::get_table_headers();
        $this->define_columns(array_keys($columnheaders));
        $this->define_headers(array_values($columnheaders));
        // TODO help icons.
        /*$helpicons = $this->get_table_help_icons();
        $this->define_help_for_headers($helpicons);*/
        $this->column_class = array_combine(array_keys($columnheaders),
            array_fill(0, count($columnheaders), 'header c6'));
        $this->column_nosort = array_keys($columnheaders);
        $this->is_downloadable(true);
        $this->define_baseurl(new moodle_url('/report/fundae/userdetails.php', [
            'courseid' => $this->courseid,
            'userid' => $this->userid,
        ]));
        $course = get_course($courseid);
        $user = core_user::get_user($userid);
        $filename = get_string('details', 'report_fundae');
        $filename .= "-$course->fullname";
        $filename .= "-" . fullname($user);
        $this->is_downloading($download, $filename);
    }

    /**
     * @param stdClass $data The row data.
     * @return string
     * @throws coding_exception
     */
    public static function col_name(stdClass $data): string {
        if (!empty($data->name)) {
            return format_string($data->name);
        }
        return get_string('nodata', 'report_fundae');
    }

    /**
     * @param stdClass $data The row data.
     * @return string
     * @throws coding_exception
     */
    public static function col_timeelapsed(stdClass $data): string {
        if (!isset($data->timeelapsed)) {
            return get_string('nodata', 'report_fundae');
        }
        return format_report_time($data->timeelapsed);
    }

    /**
     * @param stdClass $data The row data.
     * @return string
     * @throws coding_exception
     */
    public static function col_screentime(stdClass $data): string {
        if (!isset($data->screentime)) {
            return get_string('nodata', 'report_fundae');
        }
        return format_report_time($data->screentime);
    }

    /**
     * @param stdClass $data The row data.
     * @return string
     * @throws coding_exception
     */
    public static function col_engagedtime(stdClass $data): string {
        if (!isset($data->engagedtime)) {
            return get_string('nodata', 'report_fundae');
        }
        return format_report_time($data->engagedtime);
    }

    /**
     * @param stdClass $data The row data.
     * @return string
     * @throws coding_exception
     */
    public static function col_hits(stdClass $data): string {
        if (!isset($data->hits)) {
            return get_string('nodata', 'report_fundae');
        }
        return format_string($data->hits);
    }

    /**
     * @param stdClass $data The row data.
     * @return string
     * @throws coding_exception
     */
    public static function col_firstaccess(stdClass $data): string {
        if (isset($data->firstaccess) && $data->firstaccess !== 0) {
            return userdate($data->firstaccess, get_string('strftimedatetimeshort'));
        }
        return '-';
    }

    /**
     * @param stdClass $data The row data.
     * @return string
     * @throws coding_exception
     */
    public static function col_lastaccess(stdClass $data): string {
        if (isset($data->lastaccess) && $data->firstaccess !== 0) {
            return userdate($data->lastaccess, get_string('strftimedatetimeshort'));
        }
        return '-';
    }

    /**
     *
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     * @throws moodle_exception
     */
    final public function query_db($pagesize, $useinitialsbar = true): void {
        $activities = get_activities_stats($this->courseid, $this->userid);
        $this->rawdata = $activities;
        $total = count($activities);
        $this->pagesize($total, $total);
        $this->initialbars(false);
    }

    /**
     * @return array
     * @throws coding_exception
     */
    public static function get_table_headers(): array {
        return [
            'name' => get_string('activityname', 'report_fundae'),
            'timeelapsed' => get_string('dedicationtime', 'report_fundae'),
            'hits' => get_string('hits', 'report_fundae'),
            'firstaccess' => get_string('activityfirstaccess', 'report_fundae'),
            'lastaccess' => get_string('activitylastaccess', 'report_fundae'),
        ];
    }

    /**
     * @param stdClass $row
     * @return string[]
     * @throws coding_exception
     */
    public static function row_formatter(stdClass $row): array {
        return [
            self::col_name($row),
            self::col_timeelapsed($row),
            self::col_hits($row),
            self::col_firstaccess($row),
            self::col_lastaccess($row),
        ];
    }

    /**
     * @return array
     */
    final public function get_table_help_icons(): array {
        // TODO define help icons text.
        return [
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        ];
    }
}
