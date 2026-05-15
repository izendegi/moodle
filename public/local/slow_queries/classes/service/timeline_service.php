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
 * Timeline service
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\service;

use coding_exception;
use core\exception\moodle_exception;
use dml_exception;
use local_slow_queries\repository\queries_repository;
use moodle_url;

/**
 * Class timeline_service
 */
class timeline_service {
    /**
     * Function create_html
     *
     * @param $querie
     * @param queries_repository $repo
     * @return bool|string
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function create_html($querie, queries_repository $repo) {
        global $OUTPUT;

        // Timeline rendering settings.
        $secpx = .1;           // 1 second = 3px (visual scale).
        $maxwidthpx = 280;    // Cap a single bar width.
        $minwidthpx = 3;      // Minimum visible width.

        // Build last 7 days day-starts in user timezone (calendar-like lines).
        $todaystart = usergetmidnight(time());
        $daystarts = [];
        for ($i = 6; $i >= 0; $i--) {
            $daystarts[] = $todaystart - ($i * DAYSECS);
        }
        $from = $daystarts[0];
        $to = $todaystart + DAYSECS;

        // Load all occurrences of this SQL in the period (best-effort LIKE matching).
        $queries = $repo->get_for_sql_like_period($from, $to, $querie->sqltext);

        // Prepare day buckets.
        $buckets = [];
        foreach ($daystarts as $time) {
            $buckets[$time] = [
                "day" => date("Y-m-d", $time),
                "daylabel" => userdate($time, get_string('strftimedate', 'langconfig')),
                "segments" => [],
            ];
        }

        $totalcount = 0;
        $totaltime = 0;

        foreach ($queries as $querie) {
            $daystart = usergetmidnight($querie->timelogged);
            $key = $daystart;

            if (!array_key_exists($key, $buckets)) {
                continue;
            }

            $secondsinday = max(0, min(DAYSECS - 1, $querie->timelogged - $daystart));
            $leftpct = ($secondsinday / DAYSECS) * 100.0;

            $widthpx = round($querie->exectime * $secpx);
            $widthpx = max($minwidthpx, min($maxwidthpx, $widthpx));

            $totalcount++;
            $totaltime += $querie->exectime;

            $time = userdate($querie->timelogged, "%H:%M:%S");

            $buckets[$key]["segments"][] = [
                "left" => $leftpct,
                "width" => $widthpx,
                "title" => "{$time} • " . format_float($querie->exectime, 3) . "s",
            ];
        }

        // Sort segments by left position inside each day.
        foreach ($buckets as &$b) {
            usort($b["segments"], function(array $a, array $c): int {
                return ($a["left"] <=> $c["left"]);
            });
        }
        foreach ($buckets as $id => $bucket) {
            if (empty($bucket['segments'])) {
                unset($buckets[$id]);
            }
        }
        uasort($buckets, function($a, $b) {
            return strcmp($b['day'], $a['day']);
        });

        $mustachetemplate = [
            "days" => array_values($buckets),
            "totalcount" => $totalcount,
            "totaltime" => self::format_duration($totaltime),
            "secpx" => $secpx,
        ];
        return $OUTPUT->render_from_template("local_slow_queries/timeline", $mustachetemplate);
    }

    /**
     * Formats seconds into a human-friendly string using language strings.
     *
     * @param int $seconds Total seconds.
     * @return string Human readable duration (e.g. "2 days 3 hours 10 minutes 1.250 seconds").
     * @throws coding_exception
     */
    private static function format_duration(int $seconds): string {
        $seconds = max(0.0, $seconds);

        $days = floor($seconds / 86400);
        $seconds -= ($days * 86400);

        $hours = floor($seconds / 3600);
        $seconds -= ($hours * 3600);

        $minutes = floor($seconds / 60);
        $seconds -= ($minutes * 60);

        $sec = format_float($seconds, 0);

        $parts = [];

        if ($days > 0) {
            $parts[] = get_string("duration_days", "local_slow_queries", $days);
        }
        if ($hours > 0 || $days > 0) {
            $parts[] = get_string("duration_hours", "local_slow_queries", $hours);
        }
        if ($minutes > 0 || $hours > 0 || $days > 0) {
            $parts[] = get_string("duration_minutes", "local_slow_queries", $minutes);
        }

        $parts[] = get_string("duration_seconds", "local_slow_queries", $sec);

        return implode(" ", $parts);
    }
}
