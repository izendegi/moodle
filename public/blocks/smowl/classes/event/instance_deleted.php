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
 * Event instance deleted File.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance_deleted extends \core\event\base {

    /**
     * Constructs init function.
     */
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Delete instance function.
     * @param int $contextid
     * @param int  $blockinstance
     * @return object
     */
    public static function delete_instance($contextid, $blockinstance) {
        return parent::create(['contextid' => $contextid, 'other' => ['blockinstance' => $blockinstance]]);
    }

    /**
     * Get event name.
     * @return string
     */
    public static function get_name() {
        return \get_string('eventinstancedeleted', 'block_smowl');
    }

    /**
     * Get event description.
     * @return string
     */
    public function get_description() {
        return \get_string('deleteblockinstance', 'block_smowl').$this->other['blockinstance'];
    }

    /**
     * Validate event.
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['blockinstance'])) {
            throw new \coding_exception('The \'blockinstance\' value must be set in other.');
        }
    }
}
