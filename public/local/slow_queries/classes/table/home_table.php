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
 * home_table.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\table;

use local_slow_queries\service\backtrace_service;
use coding_exception;
use core\exception\moodle_exception;
use core_text;
use html_writer;
use moodle_url;
use stdClass;
use table_sql;

/**
 * Home grouped table: showing count and AVG(exectime).
 */
class home_table extends table_sql {
    /**
     * Constructor.
     *
     * @param string $uniqueid Unique table id.
     * @param moodle_url $baseurl Base URL for paging/sorting.
     * @throws coding_exception
     */
    public function __construct(string $uniqueid, moodle_url $baseurl) {
        parent::__construct($uniqueid);

        $this->define_baseurl($baseurl);

        $this->define_columns(["cnt", "sqlpreview", "origin", "avgtime", "iscron"]);
        $this->define_headers([
            get_string("col_count", "local_slow_queries"),
            get_string("col_sqlpreview", "local_slow_queries"),
            get_string("col_origin", "local_slow_queries"),
            get_string("col_avgtime", "local_slow_queries"),
            get_string("col_cron", "local_slow_queries"),
        ]);

        $this->sortable(true, "avgtime", SORT_DESC);
        $this->no_sorting("sqlpreview");
        $this->no_sorting("origin");
        $this->no_sorting("iscron");

        $this->set_attribute("class", "generaltable table-striped table-hover");
        $this->column_class("sqlpreview", "text-break");
        $this->column_class("origin", "");
        $this->column_class("cnt", "text-nowrap");
        $this->column_style("avgtime", "white-space", "nowrap");
        $this->column_style("iscron", "width", "80px");
    }

    /**
     * Renders SQL preview (clickable to detail.php using the sample id).
     *
     * @param stdClass $querie Row.
     * @return string HTML.
     * @throws moodle_exception
     */
    public function col_sqlpreview($querie): string {
        $url = new moodle_url("/local/slow_queries/detail.php", ["id" => $querie->id]);

        $sql = preg_replace("/\s+/", " ", $querie->sqltext);
        $sql = trim($sql);

        $preview = core_text::substr($sql, 0, 220);
        if (core_text::strlen($sql) > 220) {
            $preview .= "…";
        }

        return "<div class=\"position-relative\">
                    <a class=\"stretched-link text-decoration-none\" href=\"{$url->out(false)}\"></a>
                    <code class=\"lsq-sql-preview\">" . s($preview) . "</code>
                </div>";
    }

    /**
     * Renders backtrace origin line (best-effort): first line not inside /lib/dml/.
     *
     * @param stdClass $querie Row.
     * @return string HTML.
     */
    public function col_origin($querie): string {
        global $DB;

        $origin = backtrace_service::get_origin_line($querie->backtrace ?? "");

        $sql = "
            SELECT *
              FROM {local_slow_queries_comments}
             WHERE sqltext = :sqltext";
        $comments = $DB->get_record_sql($sql, ["sqltext" => $querie->sqltext]);

        if ($comments) {
            return
                html_writer::tag("span", s($origin), ["class" => "text-nowrap-width"]) .
                html_writer::tag("span", s($comments->comments), ["class" => "text-nowrap-width small text-muted"]);
        }
        return html_writer::tag("span", s($origin), ["class" => "text-nowrap-width"]);
    }

    /**
     * Renders count of occurrences.
     *
     * @param stdClass $querie Row.
     * @return string HTML.
     */
    public function col_cnt($querie): string {
        return html_writer::tag("span", ((int) $querie->cnt), ["class" => "fw-semibold"]);
    }

    /**
     * Renders average execution time in seconds.
     *
     * @param stdClass $querie Row.
     * @return string HTML.
     */
    public function col_avgtime($querie): string {
        return html_writer::tag("span", format_float((float) $querie->avgtime, 5) . "s", ["class" => "fw-semibold"]);
    }

    /**
     * Renders CRON flag.
     *
     * @param stdClass $querie Row.
     * @return string HTML.
     */
    public function col_iscron($querie): string {
        $iscron = !empty($querie->iscron);
        if (!$iscron) {
            return "";
        }

        return html_writer::tag("span", "✓", ["class" => "badge bg-success"]);
    }
}
