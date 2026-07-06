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
 * Services File.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_smowl_manage_proctored_activities' => [
        'classname'     => 'block_smowl\external\manage_proctored_activities',
        'methodname'    => 'manage_proctored_activities',
        'description'   => 'Update proctored activities',
        'type'          => 'write',
        'capabilities'  => 'block/smowl:manageactivities',
    ],
    'block_smowl_get_proctored_activities' => [
        'classname'     => 'block_smowl\external\get_proctored_activities',
        'methodname'    => 'get_proctored_activities',
        'description'   => 'Get proctored activities',
        'type'          => 'read',
        'capabilities'  => 'block/smowl:manageactivities',
    ],
];

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = [
    'Smowl Services' => [
        'shortname' => 'SMOWL_SERVICE',
        'functions' => [
            'block_smowl_manage_proctored_activities',
            'block_smowl_get_proctored_activities',
            'mod_quiz_get_quizzes_by_courses',
            'core_course_get_contents',
            'core_enrol_get_enrolled_users',
        ],
        'capabilities' => [
            'block/smowl:manageactivities',
            'moodle/course:viewhiddencourses',
            'moodle/user:viewdetails',
            'moodle/user:viewhiddendetails',
            'moodle/course:useremail',
            'moodle/user:update',
            'moodle/site:accessallgroups',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];
