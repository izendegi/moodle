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

namespace report_fundae\reportbuilder\datasource;

use cache;
use coding_exception;
use core_reportbuilder\datasource;
use core_reportbuilder\local\entities\course;
use report_fundae\reportbuilder\local\entities\fundae_user;
use report_fundae\reportbuilder\local\entities\fundae_user_time;
use report_fundae\reportbuilder\local\entities\fundae_course_completion;
use report_fundae\reportbuilder\local\entities\fundae_course_enrolment;
use report_fundae\reportbuilder\local\entities\fundae_user_access;
use report_fundae\reportbuilder\local\entities\fundae_user_details;
use report_fundae\reportbuilder\local\entities\fundae_user_messaging;

defined('MOODLE_INTERNAL') || die();

/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

class report_fundae extends datasource {

    /**
     * Get the visible name of the report.
     *
     * @return string
     * @throws coding_exception
     */
    public static function get_name(): string {
        return get_string('pluginname', 'report_fundae');
    }

    /**
     * Initialise report
     */
    protected function initialise(): void {
        $this->set_main_table('user', 'u');
        $this->add_base_condition_sql('u.suspended = 0');
        $this->add_base_condition_sql('u.deleted = 0');

        $this->add_join('JOIN ('.
            'SELECT uex.userid, ex.courseid, '.
            'MAX(CASE WHEN uex.timestart = 0 THEN uex.timecreated ELSE uex.timestart END) AS lastenroldate '.
            'FROM {user_enrolments} uex, {enrol} ex '.
            'WHERE uex.enrolid = ex.id '.
            'GROUP BY uex.userid, ex.courseid '.
            ') uelast ON uelast.userid = u.id');

        $this->add_join('JOIN {course} c ON uelast.courseid = c.id');

        $this->set_downloadable(true);
        $this->set_columns();
        $this->add_all_from_entities();
    }


    /**
     * @return void
     * @throws coding_exception
     */
    protected function set_columns(): void {
        $this->add_entity(new fundae_user_time());
        $this->add_entity(new fundae_user());
        $courseenrolmententity = (new fundae_course_enrolment())
            ->add_join('JOIN {user_enrolments} ue ON ue.userid = u.id ')
            ->add_join('JOIN {enrol} e ON e.id = ue.enrolid AND e.courseid = c.id')
            ->set_table_alias('user_enrolments', 'ue')
            ->set_table_alias('enrol', 'e');
        $this->add_entity($courseenrolmententity);

        $this->add_entity(new course());

        $completionentity = new fundae_course_completion();
        $completionentity
            ->add_join('LEFT JOIN {course_completions} cc ON cc.course = c.id AND cc.userid = u.id')
            ->set_table_alias('course_completion', 'cc')
            ->set_table_alias('course', 'c');
        $completionentity
            ->set_last_enroldate_field_sql('uelast.lastenroldate');
        $this->add_entity($completionentity);

        $this->add_entity(new fundae_user_messaging());
        $this->add_entity((new fundae_user_access())
            ->add_join('LEFT JOIN {user_lastaccess} ula ON ula.userid = u.id AND ula.courseid = c.id')
            ->set_table_alias('user_lastaccess', 'ula'));
        $this->add_entity(new fundae_user_details());
    }

    /**
     * @return string[]
     */
    public function get_default_columns(): array {
        global $CFG;
        if (str_contains($CFG->release, '4.3') || str_contains($CFG->release, '4.4') || str_contains($CFG->release, '4.5') || str_contains($CFG->release, '5.0')) {
            if (file_exists($CFG->dirroot . '/mod/zoom/locallib.php')) {
                return [
                    'fundae_user:fullnamewithpicture',
                    'fundae_user:lastname',
                    'fundae_user:firstname',
                    'course:coursefullnamewithlink',
                    'fundae_course_completion:progresspercent',
                    'fundae_course_completion:completed',
                    'fundae_user_time:timeoncourse',
                    'fundae_user_time:timeactivities',
                    'fundae_user_time:timescorms',
                    'fundae_user_time:timezoom',
                    'fundae_user_time:numberofsessions',
                    'fundae_user_time:daysonline',
                    'fundae_user_time:ratioonline',
                    'fundae_user_messaging:messagestostudents',
                    'fundae_user_messaging:messagestoteachers',
                    'fundae_user_access:firstaccess',
                    'fundae_user_access:lastaccess',
                    'course:startdate',
                    'course:enddate',
                    'fundae_user_details:details'
                ];
            }
            return [
                'fundae_user:fullnamewithpicture',
                'fundae_user:lastname',
                'fundae_user:firstname',
                'course:coursefullnamewithlink',
                'fundae_course_completion:progresspercent',
                'fundae_course_completion:completed',
                'fundae_user_time:timeoncourse',
                'fundae_user_time:timeactivities',
                'fundae_user_time:timescorms',
                'fundae_user_time:numberofsessions',
                'fundae_user_time:daysonline',
                'fundae_user_time:ratioonline',
                'fundae_user_messaging:messagestostudents',
                'fundae_user_messaging:messagestoteachers',
                'fundae_user_access:firstaccess',
                'fundae_user_access:lastaccess',
                'course:startdate',
                'course:enddate',
                'fundae_user_details:details'
            ];
        }

        if (file_exists($CFG->dirroot . '/mod/zoom/locallib.php')) {
            return [
                'fundae_user:fullnamewithpicture',
                'fundae_user:lastname',
                'fundae_user:firstname',
                'course:coursefullnamewithlink',
                'course_completion:progresspercent',
                'course_completion:completed',
                'user_time:timeoncourse',
                'user_time:timeactivities',
                'user_time:timescorms',
                'user_time:timezoom',
                'user_time:numberofsessions',
                'user_time:daysonline',
                'user_time:ratioonline',
                'user_messaging:messagestostudents',
                'user_messaging:messagestoteachers',
                'user_access:firstaccess',
                'user_access:lastaccess',
                'course:startdate',
                'course:enddate',
                'user_details:details'
            ];
        }
        return [
            'fundae_user:fullnamewithpicture',
            'fundae_user:lastname',
            'fundae_user:firstname',
            'course:coursefullnamewithlink',
            'course_completion:progresspercent',
            'course_completion:completed',
            'user_time:timeoncourse',
            'user_time:timeactivities',
            'user_time:timescorms',
            'user_time:numberofsessions',
            'user_time:daysonline',
            'user_time:ratioonline',
            'user_messaging:messagestostudents',
            'user_messaging:messagestoteachers',
            'user_access:firstaccess',
            'user_access:lastaccess',
            'course:startdate',
            'course:enddate',
            'user_details:details'
        ];
    }

    /**
     * @return array|string[]
     */
    public function get_default_filters(): array {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function get_default_conditions(): array {
        return [];
    }
}
