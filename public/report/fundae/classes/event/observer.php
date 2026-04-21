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
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

namespace report_fundae\event;

use coding_exception;
use context_system;
use core\event\config_log_created;
use dml_exception;

/**
 * An event observer.
 * @copyright  2016 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * @param config_log_created $event
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function config_updated(config_log_created $event) {
        $context = context_system::instance();
        $roles = [];
        if ($event->other['name'] === 'studentsseereports') {
            $roles = get_archetype_roles('student');
        }
        if ($event->other['name'] === 'teachersseereports') {
            $teachers = get_archetype_roles('teacher');
            foreach ($teachers as $teacher) {
                $roles[] = $teacher;
            }
            $editingteachers = get_archetype_roles('editingteacher');
            foreach ($editingteachers as $editingteacher) {
                $roles[] = $editingteacher;
            }
        }
        if (!empty($roles)) {
            if ((int)$event->other['value'] === 0) {
                foreach($roles as $rol) {
                    if ($event->other['name'] === 'studentsseereports') {
                        assign_capability('moodle/site:viewreports', CAP_INHERIT, $rol->id, $context, true);
                    }
                    assign_capability('report/fundae:view', CAP_PROHIBIT, $rol->id, $context, true);
                }
            }
            if ((int)$event->other['value'] === 1) {
                foreach($roles as $rol) {
                    if ($event->other['name'] === 'studentsseereports') {
                        assign_capability('moodle/site:viewreports', CAP_ALLOW, $rol->id, $context, true);
                    }
                    assign_capability('report/fundae:view', CAP_ALLOW, $rol->id, $context, true);
                }
            }
        }
    }
}
