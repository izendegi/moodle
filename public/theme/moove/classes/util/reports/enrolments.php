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
 * Enrolments utilitu class for theme_moove.
 *
 * @package    theme_moove
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_moove\util\reports;

defined('MOODLE_INTERNAL') || die();

/**
 * Theme moove enrolments utility class.
 *
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class enrolments {
    public function get_total_enrolments($startdate = null, $enddate = null) {
        global $DB;

        $sql = 'SELECT count(id) as qtd FROM {user_enrolments} ';

        $params = [];
        if ($startdate && $enddate) {
            $sql .= ' WHERE timecreated BETWEEN :startdate AND :enddate';

            $params = [
                'startdate' => $startdate,
                'enddate' => $enddate
            ];
        }

        return $DB->count_records_sql($sql, $params);
    }

    public function get_enrolments_groupedby_course($startdate = null, $enddate = null, $limit = null) {
        global $DB;

        $sql = 'SELECT c.shortname, count(c.id) as qtd
                FROM {user_enrolments} ue
                INNER JOIN {enrol} e ON e.id = ue.enrolid
                INNER JOIN {course} c ON c.id = e.courseid ';

        $params = [];
        if ($startdate && $enddate) {
            $sql .= ' WHERE ue.timecreated BETWEEN :startdate AND :enddate ';

            $params = [
                'startdate' => $startdate,
                'enddate' => $enddate
            ];
        }

        $sql .= ' GROUP BY c.id, c.shortname ORDER BY qtd DESC ';

        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }

        return  $DB->get_records_sql($sql, $params);
    }

    public function get_new_enrolments_chart($startdate = null, $enddate = null) {

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

            $data[$monthname] = $this->get_total_enrolments($firstdate, $lastdate);

            $enddate->modify('+1 month');
        }

        $chart = new \core\chart_line();
        $chart->set_smooth(true);
        $series = new \core\chart_series(get_string('graph_newenrolments', 'theme_moove'), array_values($data));
        $series->set_type(\core\chart_series::TYPE_LINE);
        $chart->add_series($series);
        $chart->set_labels(array_keys($data));

        return $chart;
    }

    public function get_top_courses_enrolments_chart($limit = 10) {
        $data = $this->get_enrolments_groupedby_course(null, null, $limit);

        if (!$data) {
            return false;
        }

        $seriesdata = [];
        $labels = [];
        foreach ($data as $item) {
            $labels[] = $item->shortname;
            $seriesdata[] = $item->qtd;
        }

        $chart = new \core\chart_bar();
        $series = new \core\chart_series(get_string('graph_topenrolmentcourses', 'theme_moove'), $seriesdata);
        $chart->add_series($series);
        $chart->set_labels($labels);
        $chart->set_horizontal(true);

        return $chart;
    }
}
