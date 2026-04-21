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

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/theme/moove/lib.php');

/**
 * @param $theme
 * @return string
 * @throws coding_exception
 */
function theme_mondragon_get_extra_scss($theme): string {
    $mooveconfig = theme_config::load('moove');
    return theme_moove_get_extra_scss($mooveconfig);
}

/**
 * @param $theme
 * @return string
 * @throws coding_exception
 */
function theme_mondragon_get_pre_scss($theme): string {
    $mooveconfig = theme_config::load('moove');
    return theme_moove_get_pre_scss($mooveconfig);
}

function theme_mondragon_get_precompiled_css(): string {
    global $CFG;
    return theme_moove_get_precompiled_css();
}

/**
 * @param $theme
 * @return string
 * @throws coding_exception
 */
function theme_mondragon_get_main_scss_content($theme): string {
    global $CFG;
    $mooveconfig = theme_config::load('moove');
    $moovescss = theme_moove_get_main_scss_content($mooveconfig);
    $mondragon = file_get_contents($CFG->dirroot . '/theme/mondragon/scss/mondragon.scss');
    return $moovescss . "\n" . $mondragon;
}

/**
 * @param $course
 * @param $cm
 * @param $context
 * @param $filearea
 * @param $args
 * @param $forcedownload
 * @param array $options
 * @return bool|void
 * @throws coding_exception
 * @throws moodle_exception
 */
function theme_mondragon_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    $theme = theme_config::load('moove');

    if ($context->contextlevel === CONTEXT_SYSTEM &&
        ($filearea === 'logo' || $filearea === 'loginbgimg' || $filearea == 'favicon')) {
        $theme = theme_config::load('moove');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }

    if ($context->contextlevel === CONTEXT_SYSTEM && preg_match("/^sliderimage[1-9][0-9]?$/", $filearea) !== false) {
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }

    if ($context->contextlevel === CONTEXT_SYSTEM && $filearea === 'marketing1icon') {
        return $theme->setting_file_serve('marketing1icon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel === CONTEXT_SYSTEM && $filearea === 'marketing2icon') {
        return $theme->setting_file_serve('marketing2icon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel === CONTEXT_SYSTEM && $filearea === 'marketing3icon') {
        return $theme->setting_file_serve('marketing3icon', $args, $forcedownload, $options);
    }

    if ($context->contextlevel === CONTEXT_SYSTEM && $filearea === 'marketing4icon') {
        return $theme->setting_file_serve('marketing4icon', $args, $forcedownload, $options);
    }

    send_file_not_found();
}