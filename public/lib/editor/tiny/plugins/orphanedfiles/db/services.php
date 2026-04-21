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
 * Tiny text editor orphanedfiles Plugin version file.
 *
 * @package    tiny_orphanedfiles
 * @copyright  2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @copyright  2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @author     2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @author     2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
        'tiny_orphanedfiles_getalldraftfiles' => [
                'classname' => 'tiny_orphanedfiles\external\getalldraftfiles',
                'description' => 'Returns all draft files in draftfile area of the editor',
                'type' => 'read',
                'ajax' => true,
                'loginrequired' => true,
        ],
        'tiny_orphanedfiles_deletedraftfiles' => [
                'classname' => 'tiny_orphanedfiles\external\deletedraftfiles',
                'description' => 'Deletes selected draft files in draftfile area of the editor',
                'type' => 'write',
                'ajax' => true,
                'loginrequired' => true,
        ],
];
