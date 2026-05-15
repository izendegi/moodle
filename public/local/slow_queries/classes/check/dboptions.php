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
 * check dboptions
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\check;

/**
 * Class dboptions
 */
class dboptions {
    /**
     * Function test_logslow
     *
     * @return array
     */
    public static function test_logslow() {
        global $CFG;
        $logslowraw = null;
        if (!empty($CFG->dboptions) && is_array($CFG->dboptions) && array_key_exists("logslow", $CFG->dboptions)) {
            $logslowraw = $CFG->dboptions["logslow"];
        }

        $showlogslowwarning = false;
        if ($logslowraw === true) {
            $showlogslowwarning = true;
        } else if (is_numeric($logslowraw) && (float) $logslowraw > 0) {
            $showlogslowwarning = true;
        } else if (is_string($logslowraw) && strtolower(trim($logslowraw)) === "true") {
            $showlogslowwarning = true;
        }

        $logslowvalue = "";
        if (is_bool($logslowraw)) {
            $logslowvalue = $logslowraw ? "true" : "false";
        } else if ($logslowraw !== null) {
            $logslowvalue = $logslowraw;
        }

        $logslowconfigsnippet = "....\n";
        $logslowconfigsnippet .= "\$CFG->prefix    = 'mdl_';\n";
        $logslowconfigsnippet .= "\$CFG->dboptions = array(\n";
        $logslowconfigsnippet .= "    ....\n";
        $logslowconfigsnippet .= "    'logslow'     => 3, // 3s.\n";
        $logslowconfigsnippet .= "    ....\n";
        $logslowconfigsnippet .= ");\n";
        $logslowconfigsnippet .= "...\n";

        return [
            "showlogslowwarning" => $showlogslowwarning,
            "logslowvalue" => $logslowvalue,
            "logslowconfigsnippet" => $logslowconfigsnippet,
        ];
    }
}
