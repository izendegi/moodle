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
 * The Filter Gitquotes Main Class.
 *
 * @package   filter_gitquotes
 * @copyright 2024 devrdn rrdninc@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// The actual implementation is under classes/text_filter.php
// This file is just a workaround to make the filter work
// in Moodle versions below 4.5.
class_alias(\filter_gitquotes\text_filter::class, \filter_gitquotes::class);