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
 * Helper class
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package    block_user_favorites
 * @copyright  26-10-2018 MFreak.nl
 * @author     Luuk Verhoeven
 **/

namespace block_user_favorites;

use coding_exception;
use dml_exception;

/**
 * Class helper
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  26-10-2018 MFreak.nl
 */
class helper {

    /**
     * We are in DEBUG mode display more info than general.
     *
     * @return bool
     */
    public static function has_debugging_enabled(): bool {
        global $CFG;

        // Check if the environment has debugging enabled.
        return ($CFG->debug >= 32767 && $CFG->debugdisplay == 1);
    }

    /**
     * Convert user_preference entries to a separate table
     * We will keep the original serialized data for now
     *
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function convert_users_preference_favorites(): void {
        global $DB;

        $rs = $DB->get_recordset('user_preferences', [
            'name' => 'user_favorites',
        ]);

        foreach ($rs as $row) {
            $favorites = @unserialize($row->value);

            if (empty($favorites)) {
                continue;
            }

            $favoriteinstance = new favorites($row->userid);
            array_walk($favorites, [$favoriteinstance, 'update_favorite'], $row->userid);

        }
        $rs->close();
    }

}
