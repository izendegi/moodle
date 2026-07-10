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
 * Users utilitu class for theme_moove.
 *
 * @package    theme_moove
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_moove\util\reports;

use core\chart_line;

defined('MOODLE_INTERNAL') || die();

/**
 * Eskada users utility class.
 *
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class users {
    /**
     * Returns the total of active users.
     *
     * @return int
     * @throws \dml_exception
     */
    public function get_totalactiveusers() {
        global $DB;

        return $DB->count_records('user', ['deleted' => 0, 'suspended' => 0]) - 1;
    }

    /**
     * Returns the total of online users.
     *
     * @return int
     * @throws \dml_exception
     */
    public function get_totalonlineusers() {
        $onlineusers = new \block_online_users\fetcher(null, time(), 300, null, CONTEXT_SYSTEM, null);

        return $onlineusers->count_users();
    }

    public function get_total_users($startdate, $enddate) {
        global $DB;

        $sql = 'SELECT count(id) as qtd FROM {user} WHERE timecreated BETWEEN :startdate AND :enddate';

        return $DB->count_records_sql($sql, ['startdate' => $startdate, 'enddate' => $enddate]);
    }

    public function get_new_users_chart($startdate = null, $enddate = null) {

        if ($startdate == null) {
            $startdate = new \DateTime(date('Y-m-d H:i:s'));
            $startdate->modify('first day of this month');
        }

        if ($enddate == null) {
            $enddate = clone $startdate;

            $enddate = $enddate->sub(new \DateInterval('P5M'));
        }

        $diff = $startdate->diff($enddate);
        $monthsdiff = (($diff->format('%y') * 12) + $diff->format('%m')) + 1;

        $data = [];
        for ($i = 1; $i <= $monthsdiff; $i++) {
            $monthname = $enddate->format('F');

            $firstdate = strtotime($enddate->format('Y-m-d'));
            $lastdate = strtotime($enddate->format('Y-m-t'));

            $data[$monthname] = $this->get_total_users($firstdate, $lastdate);

            $enddate->modify('+1 month');
        }

        $chart = new \core\chart_line();
        $chart->set_smooth(true);
        $series = new \core\chart_series(get_string('graph_newusers', 'theme_moove'), array_values($data));
        $series->set_type(\core\chart_series::TYPE_LINE);
        $chart->add_series($series);
        $chart->set_labels(array_keys($data));

        return $chart;
    }

    /**
     * Returns online users graph
     *
     * @return chart_line
     * @throws \dml_exception
     */
    public function get_onlineusers_chart($starttime, $endtime) {
        list($all, $unique) = $this->get_log_access_by_minute($starttime, $endtime);

        if (is_null($all) || is_null($unique)) {
            return null;
        }

        $chart = new \core\chart_line();
        $chart->set_smooth(true);
        $chart->add_series(new \core\chart_series('Total actions', array_values($all)));
        $chart->add_series(new \core\chart_series('Unique users', array_values($unique)));
        $chart->set_labels(array_keys($all));

        return $chart;
    }

    /**
     * Get last 60 minutes access logs
     *
     * @return array[]|false
     * @throws \dml_exception
     */
    public function get_log_access_by_minute(\DateTime $starttime, \DateTime $endtime) {
        global $DB;

        $sql = 'SELECT id, userid, MINUTE(FROM_UNIXTIME(timecreated)) as minute
                FROM {logstore_standard_log}
                WHERE timecreated > :starttime AND timecreated < :endtime';

        $records = $DB->get_records_sql($sql, [
            'starttime' => $starttime->getTimestamp(),
            'endtime' => $endtime->getTimestamp()
        ]);

        if (!$records) {
            return false;
        }

        $all = [];
        $users = [];
        $unique = [];
        foreach ($records as $record) {
            $all[$record->minute] = isset($all[$record->minute]) ? ($all[$record->minute] + 1) : 1;

            if (!isset($users[$record->minute])) {
                $users[$record->minute][] = $record->userid;

                continue;
            }

            if (array_search($record->userid, $users[$record->minute]) !== false) {
                continue;
            }

            array_push($users[$record->minute], $record->userid);
        }

        foreach ($users as $key => $user) {
            $unique[$key] = count($user);
        }

        return [$all, $unique];
    }
}
