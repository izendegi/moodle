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
 * Web service
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package    block_user_favorites
 * @copyright  26-10-2018 MFreak.nl
 * @author     Luuk Verhoeven
 **/
defined('MOODLE_INTERNAL') || die;

$functions = [

    'block_user_favorites_set_url' => [
        'classname' => \block_user_favorites\external::class,
        'methodname' => 'set_url',
        'classpath' => '',
        'description' => 'Set the url for a user favourite',
        'type' => 'write',
        'loginrequired' => true,
        'ajax' => true,
    ],

    'block_user_favorites_delete_url' => [
        'classname' => \block_user_favorites\external::class,
        'methodname' => 'delete_url',
        'classpath' => '',
        'description' => 'Delete the url of a user favorite',
        'type' => 'write',
        'loginrequired' => true,
        'ajax' => true,
    ],

    'block_user_favorites_content' => [
        'classname' => \block_user_favorites\external::class,
        'methodname' => 'get_content',
        'classpath' => '',
        'description' => 'Get HTML content block',
        'type' => 'read',
        'loginrequired' => true,
        'ajax' => true,
    ],

    'block_user_favorites_set_order' => [
        'classname' => \block_user_favorites\external::class,
        'methodname' => 'set_order',
        'classpath' => '',
        'description' => 'Set the order of the user\'s favourites',
        'type' => 'write',
        'loginrequired' => true,
        'ajax' => true,
    ],
];
