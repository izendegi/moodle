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
 * Block accesrulesmowlcheckcam setting class.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_smowl_admin_setting_accesrulesmowlcheckcam extends admin_setting_configselect {

    /**
     * Active or de active quiz accessrule extension in all quizes.
     * @param boolean $data
     * @return boolean
     */
    public function write_setting($data) {
        if ($data) {
            block_smowl_accessrule_smowlcheckcam_active_all();
        } else {
            block_smowl_accessrule_smowlcheckcam_disable_all();
        }

        return parent::write_setting($data);
    }
}
