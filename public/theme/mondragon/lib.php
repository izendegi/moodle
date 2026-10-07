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
 * Theme callbacks.
 *
 * Every callback hands over to Moove with Moove's own config, so the settings of Moove (brand
 * colour, logo, presets, raw SCSS…) keep driving this theme.
 *
 * @package    theme_mondragon
 * @copyright  2026 3iPunt (contacte@tresipunt.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/theme/moove/lib.php');

/**
 * Returns the extra SCSS of Moove.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_mondragon_get_extra_scss($theme): string {
    $mooveconfig = theme_config::load('moove');
    return theme_moove_get_extra_scss($mooveconfig);
}

/**
 * Returns the pre SCSS of Moove.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_mondragon_get_pre_scss($theme): string {
    $mooveconfig = theme_config::load('moove');
    $scss = theme_moove_get_pre_scss($mooveconfig);
    // Moove only sets $brand-primary; without this Bootstrap keeps Boost's blue $primary.
    return $scss . "\n\$primary: \$brand-primary;\n";
}

/**
 * Returns the precompiled CSS of Moove.
 *
 * @return string
 */
function theme_mondragon_get_precompiled_css(): string {
    return theme_moove_get_precompiled_css();
}

/**
 * Returns the main SCSS of Moove followed by the SCSS of this theme.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_mondragon_get_main_scss_content($theme): string {
    global $CFG;
    $mooveconfig = theme_config::load('moove');
    $moovescss = theme_moove_get_main_scss_content($mooveconfig);
    $mondragon = file_get_contents($CFG->dirroot . '/theme/mondragon/scss/mondragon.scss');
    return $moovescss . "\n" . $mondragon;
}

/**
 * Serves the files of the theme settings, which are the files of Moove.
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param context $context The context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 * @return bool
 */
function theme_mondragon_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    return theme_moove_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options);
}
