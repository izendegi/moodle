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
 * Class handles conditional availability information for a smowl block.
 *
 * @package block_smowl
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availability_info_block extends \core_availability\info {
    /**
     * Constructs with item details.
     * @param object $block
     */
    public function __construct($block) {
        globaL $COURSE;
        parent::__construct($COURSE, true, $block->config->availabilityconditionsjson);
    }

    /**
     * Get block name function.
     * @return string
     */
    protected function get_thing_name() {
        return get_string('pluginname', 'block_smowl');
    }

    /**
     * Get context function.
     * @return int contect id
     */
    public function get_context() {
        return \context_course::instance($this->get_course()->id);
    }

    /**
     * Get capability used to view or hidden.
     * @return string capability to manage groups
     */
    protected function get_view_hidden_capability() {
        return 'block/smowl:managegroups';
    }

    /**
     * Set in database function.
     * @param string $availability
     */
    protected function set_in_database($availability) {
    }
}
