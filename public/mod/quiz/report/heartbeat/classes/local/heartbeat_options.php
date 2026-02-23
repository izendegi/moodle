<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * This file defines the settings for the heartbeat quiz report report.
 *
 * @package   quiz_heartbeat
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_heartbeat\local;

/**
 * Class to store the options for a {@see quiz_heartbeat_report}.
 *
 * @package   quiz_heartbeat
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class heartbeat_options extends \mod_quiz\local\reports\attempts_report_options {
    /** @var int whether to do ascending or descending sort */
    public int $tdir = SORT_ASC;

    /** @var string initial of first name for filtering */
    public string $tifirst = '';

    /** @var string initial of last name for filtering */
    public string $tilast = '';

    /** @var string column used for sorting */
    public string $tsort = 'lastname';

    /**
     * Constructor
     *
     * @param string $mode which report these options are for
     * @param object $quiz the settings for the quiz being reported on
     * @param object $cm the course module objects for the quiz being reported on
     * @param object $course the course settings for the coures this quiz is in
     * @return void
     */
    public function __construct($mode, $quiz, $cm, $course) {
        $this->mode = $mode;
        $this->quiz = $quiz;
        $this->cm = $cm;
        $this->course = $course;
    }

    /**
     * Get the current value of the settings to pass to the settings form.
     */
    public function get_initial_form_data() {
        $toform = new \stdClass();

        return $toform;
    }

    /**
     * Set the fields of this object from the form data. Overriding parent method, because we
     * do not have a form.
     *
     * @param object $fromform data from the settings form
     */
    public function setup_from_form_data($fromform): void {
    }

    /**
     * Set the fields of this object from the form data. Overriding parent method, because we
     * do not have a form.
     *
     */
    public function resolve_dependencies(): void {
    }

    /**
     * Set the fields of this object from the URL parameters.
     *
     * @return void
     */
    public function setup_from_params() {
        $this->tdir = optional_param('tdir', SORT_ASC, PARAM_INT);
        $this->tifirst = optional_param('tifirst', '', PARAM_ALPHA);
        $this->tilast = optional_param('tilast', '', PARAM_ALPHA);
        $this->tsort = optional_param('tsort', 'lastname', PARAM_ALPHA);
    }
}
