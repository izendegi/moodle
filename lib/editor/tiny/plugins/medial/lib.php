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
 * Tiny text editor integration version file.
 *
 * @package    tiny_medial
 * @copyright  2023 Streaming LTD
 * @author     Tim Williams (tim@medial.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/mod/helixmedia/locallib.php');
require_once($CFG->dirroot.'/lib/filterlib.php');

/**
 * Parses a list of module types and checks if they match the one we are in.
 * @param $param The list name to check
 * @return The module name string or false
 */
function tiny_medial_checklist($param) {
    global $PAGE, $DB;
    $config = get_config('tiny_medial', $param);
    $types = explode("\n", $config);
    for ($i = 0; $i < count($types); $i++) {
        $types[$i] = trim($types[$i]);
        if (strlen($types[$i]) > 0 && strpos($PAGE->pagetype, 'mod-'.$types[$i]) !== false &&
            $DB->get_record('modules', ['name' => $types[$i]])) {
            return $types[$i];
        }
    }

    return "";
}

/**
 * Test if we have an active MEDIAL filter
 * @param $context (optional) The current context
 * @return true if there is a filter
 */
function tiny_medial_has_filter($context = false) {
    if ($context !== false) {
        $filters = filter_get_active_in_context($context);
        if (array_key_exists('medial', $filters)) {
            return true;
        }

        return false;
    }

    global $DB;
    // If there is no context then we just need to know that the filter is active somewhere in Moodle.
    $rec = $DB->get_records('filter_active', ['filter' => 'medial', 'active' => 1]);
    if ($rec && count($rec) > 0) {
        return true;
    }

    return false;
}

/**
 * Gets the plugin config settings defaults.
 * @return stdclass with the defaults as properties
 **/
function tiny_medial_get_defaults() {
    // If we already have ATTO installed and the TINY config doesn't exist, use ATTO config for the initial default.
    $acfg = get_config('atto_helixatto');
    $mcfg = get_config('tiny_medial', 'hideinsert');
    if (isset($acfg->hideinsert) && $mcfg === false) {
        return $acfg;
    }

    $cfg = new \stdclass();
    $cfg->hideinsert = 1;
    $cfg->placeholder = 0;
    $cfg->modtypeperm = '';
    $cfg->uselinkdesc = "forum\r\nworkshop";
    $cfg->embedopt = 0;
    $cfg->allowfeedback = 0;
    return $cfg;
}
