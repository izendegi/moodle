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
 * Web service definitions for mod_typeform
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_typeform\mod_typeform_external;

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_typeform_get_forms' => [
        'classname'     => mod_typeform_external::class,
        'classpath'   => 'mod/typeform/classes/external.php',
        'methodname'    => 'get_forms',
        'description'   => 'Get list of Typeform forms',
        'type'          => 'read',
        'capabilities'  => 'mod/typeform:addinstance',
        'ajax'          => true,
    ],
    'mod_typeform_mark_complete' => [
        'classname'     => mod_typeform_external::class,
        'classpath'   => 'mod/typeform/classes/external.php',
        'methodname'    => 'mark_complete',
        'description'   => 'Mark typeform activity as complete',
        'type'          => 'write',
        'capabilities'  => 'mod/typeform:view',
        'ajax'          => true,
    ],
    'mod_typeform_add_event' => [
        'classname'     => mod_typeform_external::class,
        'classpath'   => 'mod/typeform/classes/external.php',
        'methodname'    => 'add_event',
        'description'   => 'Add started event',
        'type'          => 'write',
        'capabilities'  => 'mod/typeform:view',
        'ajax'          => true,
    ],
    'mod_typeform_get_workspace_forms' => [
        'classname'     => mod_typeform_external::class,
        'classpath'   => 'mod/typeform/classes/external.php',
        'methodname'    => 'get_workspace_forms',
        'description'   => 'get_workspace_forms',
        'type'          => 'write',
        'capabilities'  => 'mod/typeform:view',
        'ajax'          => true,
    ],
];
