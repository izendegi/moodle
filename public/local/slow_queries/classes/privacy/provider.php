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
 * Privacy provider for local_slow_queries.
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_slow_queries\privacy;

/**
 * Privacy provider for local_slow_queries.
 *
 * This plugin does not store any personal data. It only displays query logs
 * that already exist in the database (e.g. {log_queries}) to site administrators.
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Returns the language string identifier explaining why this plugin stores no personal data.
     *
     * @return string The language string key.
     */
    public static function get_reason(): string {
        return "privacy:metadata";
    }
}
