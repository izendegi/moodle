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

namespace block_smowl\event;

/**
 * Event instance updated File.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance_updated extends \core\event\base {

    /**
     * Constructs init function.
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Update instance function.
     * @param int $contextid
     * @param int  $blockinstance
     * @param int  $oldblockname
     * @return object
     */
    public static function update_instance($contextid, $blockinstance, $oldblockname) {
        return parent::create(['contextid' => $contextid, 'other' => [
            'blockinstance' => $blockinstance,
            'oldblockname' => $oldblockname,
        ]]);
    }

    /**
     * Get event name.
     * @return string
     */
    public static function get_name() {
        return \get_string('eventinstanceupdated', 'block_smowl');
    }

    /**
     * Get event description.
     * @return string
     */
    public function get_description() {
        return \get_string('updateblockinstance', 'block_smowl').$this->other['blockinstance'].
        \get_string('fromoldblockname', 'block_smowl').$this->other['oldblockname'];
    }

    /**
     * Validate event.
     */
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['blockinstance'])) {
            throw new \coding_exception('The \'blockinstance\' value must be set in other.');
        }
        if (!isset($this->other['oldblockname'])) {
            throw new \coding_exception('The \'oldblockname\' value must be set in other.');
        }
    }
}
