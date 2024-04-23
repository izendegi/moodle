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
 * Eskda report index
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
 * Eskada report index renderable class.
 *
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reportindex implements renderable, templatable {
    public function export_for_template(renderer_base $output) {
        $users = new users();
        $courses = new courses();
        $enrolments = new enrolments();

        $newuserschart = $users->get_new_users_chart();
        if ($newuserschart) {
            $newuserschart = $output->render($newuserschart);
        }

        $newenrolmentschart = $enrolments->get_new_enrolments_chart();
        if ($newenrolmentschart) {
            $newenrolmentschart = $output->render($newenrolmentschart);
        }

        $coursesconclusionschart = $courses->get_coursesconclusions_chart();
        if ($coursesconclusionschart) {
            $coursesconclusionschart = $output->render($coursesconclusionschart);
        }

        $topenrolmentcourseschart = $enrolments->get_top_courses_enrolments_chart();
        if ($topenrolmentcourseschart) {
            $topenrolmentcourseschart = $output->render($topenrolmentcourseschart);
        }

        $topconclusionscourseschart = $courses->get_top_courses_conclusions_chart();
        if ($topconclusionscourseschart) {
            $topconclusionscourseschart = $output->render($topconclusionscourseschart);
        }

        return [
            'totalusers' => $users->get_totalactiveusers(),
            'onlineusers' => $users->get_totalonlineusers(),
            'totalcourses' => $courses->get_totalcourses(),
            'totalenrolments' => $enrolments->get_total_enrolments(),
            'newuserschart' => $newuserschart,
            'newenrolmentschart' => $newenrolmentschart,
            'coursesconclusionschart' => $coursesconclusionschart,
            'topenrolmentcourseschart' => $topenrolmentcourseschart,
            'topconclusionscourseschart' => $topconclusionscourseschart
        ];
    }
}