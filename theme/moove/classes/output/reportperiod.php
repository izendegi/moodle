<?php
// This file is part of BBCalendar block for Moodle - http://moodle.org/
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
 * Eskda report period
 *
 * @package    theme_moove
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_moove\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use theme_moove\util\reports\courses;
use theme_moove\util\reports\enrolments;
use theme_moove\util\reports\users;
use templatable;
use renderer_base;

/**
 * Eskada report period renderable class.
 *
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reportperiod implements renderable, templatable {
    protected $startdate;
    protected $enddate;

    public function __construct($startdate, $enddate) {
        $this->startdate = $startdate;
        $this->enddate = $enddate;
    }

    public function export_for_template(renderer_base $output) {
        $users = new users();
        $courses = new courses();
        $enrolments = new enrolments();

        return [
            'startdate' => $this->startdate->format('Y-m-d'),
            'enddate' => $this->enddate->format('Y-m-d'),
            'totalusers' => $users->get_total_users($this->startdate->getTimestamp(), $this->enddate->getTimestamp()),
            'totalenrolments' => $enrolments->get_total_enrolments($this->startdate->getTimestamp(), $this->enddate->getTimestamp()),
            'totalconclusions' => $courses->get_total_conclusions($this->startdate->getTimestamp(), $this->enddate->getTimestamp()),
            'coursesenrolments' => array_values($enrolments->get_enrolments_groupedby_course($this->startdate->getTimestamp(), $this->enddate->getTimestamp())),
            'coursescompletions' => array_values($courses->get_conclusions_groupedby_course($this->startdate->getTimestamp(), $this->enddate->getTimestamp())),
        ];
    }
}