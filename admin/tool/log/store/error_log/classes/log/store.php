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
 * Standard log reader/writer.
 *
 * @package    logstore_error_log
 * @copyright  2024 Andrei Bautu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_error_log\log;

use core\event\base;
use tool_log\log\manager;
use tool_log\log\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Log store writer that sends data to PHP's error_log.
 *
 * @package logstore_error_log\log
 */
class store implements writer {
    /**
     * Construct
     *
     * @param \tool_log\log\manager $manager
     */
    public function __construct(manager $manager) {
        // Nothing to do.
    }

    /**
     * {@inheritDoc}
     */
    public function write(base $event) {
        $data = json_encode($event->get_data());
        // phpcs:ignore
        error_log("logstore_error_log: $data");
    }

    /**
     * {@inheritDoc}
     */
    public function dispose() {
        // Nothing to do.
    }
}