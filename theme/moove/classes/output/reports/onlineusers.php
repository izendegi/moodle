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
 * Moove report period
 *
 * @package    theme_moove
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_moove\output\reports;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use theme_moove\util\reports\courses;
use theme_moove\util\reports\enrolments;
use theme_moove\util\reports\users;

/**
 * Moove report period renderable class.
 *
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class onlineusers implements renderable, templatable {
    protected $starttime;
    protected $endtime;

    public function __construct($starttime, $endtime) {
        $this->starttime = $starttime;
        $this->endtime = $endtime;
    }

    public function export_for_template(renderer_base $output) {
        if ($this->endtime->getTimestamp() - $this->starttime->getTimestamp() > 3600) {
            return [
                'starttime' => $this->starttime->format('Y-m-d H:i:s'),
                'endtime' => $this->endtime->format('Y-m-d H:i:s'),
                'error' => true,
            ];
        }

        $users = new users();

        $chart = $users->get_onlineusers_chart($this->starttime, $this->endtime);

        if ($chart) {
            $chart = $output->render($chart);
        }

        return [
            'starttime' => $this->starttime->format('Y-m-d H:i:s'),
            'endtime' => $this->endtime->format('Y-m-d H:i:s'),
            'chart' => $chart,
        ];
    }
}
