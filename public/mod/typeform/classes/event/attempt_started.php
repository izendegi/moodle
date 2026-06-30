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
 * The mod_typeform course module viewed event class.
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_typeform\event;

use core\context\module;
use core\event\base;
use core\url;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_typeform course module viewed event class.
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attempt_started extends base {
    /**
     * Init method.
     */
    protected function init() {
        $this->data['objecttable'] = 'typeform_submission';
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }
    /**
     * Return localised event name.
     *
     * @return string
     * @throws coding_exception
     */
    public static function get_name(): string {
        return get_string('attemptstarted', 'mod_typeform');
    }
    /**
     * Get URL related to the action
     *
     * @return moodle_url
     * @throws moodle_exception
     */
    public function get_url(): moodle_url {
        $cmcontext = module::instance($this->contextinstanceid);
        return new url('mod/typeform/view.php', ['cmid' => $cmcontext->instanceid]);
    }

    /**
     * Used for maping events on restore
     * @return array
     */
    public static function get_objectid_mapping(): array {
        return ['db' => 'typeform_submission', 'restore' => 'typeform_submission'];
    }
}
