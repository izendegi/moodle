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
 * check performance slow_queries
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\check\performance;

use action_link;
use coding_exception;
use core\check\check;
use core\check\result;
use core\exception\moodle_exception;
use dml_exception;
use local_slow_queries\check\dboptions;
use moodle_url;

/**
 * Class slow_queries
 */
class slow_queries extends check {

    /**
     * Get the short check name
     *
     * @return string
     * @throws coding_exception
     */
    public function get_name(): string {
        return get_string("checkperformance_slowqueries_name", "local_slow_queries");
    }

    /**
     * A link to a place to action this
     *
     * @return action_link|null
     * @throws coding_exception
     */
    public function get_action_link(): ?action_link {
        $url = new moodle_url("/local/slow_queries/");
        $text = get_string("checkperformance_slowqueries_action", "local_slow_queries");
        return new action_link($url, $text);
    }

    /**
     * Return the result
     *
     * @return result object
     * @throws moodle_exception
     * @throws dml_exception
     * @throws coding_exception
     */
    public function get_result(): result {
        global $DB, $OUTPUT;

        $testlogslow = dboptions::test_logslow();
        if (empty($testlogslow["showlogslowwarning"])) {
            $summary = get_string("checkperformance_slowqueries_notconfigured", "local_slow_queries");
            $details = $OUTPUT->render_from_template("local_slow_queries/install", $testlogslow);
            return new result(result::CRITICAL, $summary, $details);
        }

        $sql = "
            SELECT
                   SUM(CASE WHEN exectime > 5  THEN 1 ELSE 0 END) AS gt5,
                   SUM(CASE WHEN exectime > 20 THEN 1 ELSE 0 END) AS gt20,
                   SUM(CASE WHEN exectime > 40 THEN 1 ELSE 0 END) AS gt40,
                   SUM(CASE WHEN exectime > 60 THEN 1 ELSE 0 END) AS gt60
              FROM {log_queries}";
        $counts = $DB->get_record_sql($sql);

        if (!empty($counts->gt5)) {
            $gt5 = (int) ($counts->gt5 ?? 0);
            $gt20 = (int) ($counts->gt20 ?? 0);
            $gt40 = (int) ($counts->gt40 ?? 0);
            $gt60 = (int) ($counts->gt60 ?? 0);

            $url = new moodle_url("/local/slow_queries/");
            $a = ["count" => $gt5, "seconds" => 5];
            $summary = get_string("checkperformance_slowqueries_summary_found", "local_slow_queries", $a);

            $lines = [];

            $a = ["count" => $gt5, "seconds" => 5, "url" => $url];
            $lines[] = "<p>" . get_string("checkperformance_slowqueries_details_found", "local_slow_queries", $a) . "</p>";

            if ($gt20 > 0) {
                $url->params(["minexec" => 20]);
                $a = ["count" => $gt20, "seconds" => 20, "url" => $url];
                $lines[] = "<p>" . get_string("checkperformance_slowqueries_details_morethan", "local_slow_queries", $a) . "</p>";
            }
            if ($gt40 > 0) {
                $url->params(["minexec" => 40]);
                $a = ["count" => $gt40, "seconds" => 40, "url" => $url];
                $lines[] = "<p>" . get_string("checkperformance_slowqueries_details_morethan", "local_slow_queries", $a) . "</p>";
            }
            if ($gt60 > 0) {
                $url->params(["minexec" => 60]);
                $a = ["count" => $gt60, "seconds" => 60, "url" => $url];
                $lines[] = "<p>" . get_string("checkperformance_slowqueries_details_morethan", "local_slow_queries", $a) . "</p>";
            }

            $details = implode("\n", $lines);

            return new result(result::ERROR, $summary, $details);
        }

        $summary = get_string("checkperformance_slowqueries_none", "local_slow_queries");

        return new result(result::OK, $summary);
    }
}
