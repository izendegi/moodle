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


use report_fundae\task\generate_periodic_reports;
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => generate_periodic_reports::class,
        'blocking' => 0,
        'minute' => 'R', // Random minute
        'hour' => '3', // At 3 AM
        'day' => '*',
        'month' => '*',
        'dayofweek' => '0', // Every sunday
    ],
];
