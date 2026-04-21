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
 * Lang import
 *
 * @package    tool_amoslink
 * @copyright  2024 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;

if ($hassiteconfig) {
    // First, retrieve all installed plugins.
    $pluginman = core_plugin_manager::instance();
    $plugininfo = $pluginman->get_plugins();
    $pluginstring = '';
    foreach ($plugininfo as $type => $plugins) {
        foreach ($plugins as $name => $plugin) {
            // If standard plugin, don't add it to list.
            if ($plugin->is_standard()) {
                continue;
            }
            // Function str_contains only exists in PHP 8.
            //Check if function exists. If not, use polyfill.
            if (!function_exists('str_contains')) {
                function str_contains(string $haystack, string $needle): bool {
                    return '' === $needle || false !== strpos($haystack, $needle);
                }
            }
            // If plugin is an activity, delete prefix _mod.
            $checkformod = str_contains($plugin->component, 'mod_');
            if ($checkformod == 1)
                {
                    $finalname = substr($plugin->component, 4);
                } else {
                    $finalname = $plugin->component;
                }
            // Create a string of all plugins separated by comma.
            $pluginstring .= $finalname.",";
        }
    }
    // Check default language.
    $lang = $CFG->lang;
    // Just delete last comma :).
    $contribs = substr($pluginstring, 0, -1);
    // Create URL that leads to AMOS with all parameters.
    $url = 'https://lang.moodle.org/local/amos/view.php?t='.time().'&v=l&l='.$lang.'&c='.$contribs.'&s&d&m=1';
    // Add admin link.
    $ADMIN->add('language', new admin_externalpage('toolamoslink', get_string('pluginname', 'tool_amoslink'), $url));
}
