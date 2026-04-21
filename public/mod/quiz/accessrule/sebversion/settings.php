<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Global configuration settings for the quizaccess_sebversion plugin.
 *
 * @package    quizaccess_sebversion
 * @author     Philipp Imhof
 * @copyright  2025, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig && $ADMIN->fulltree) {
    $settings->add(
        new admin_setting_configcheckbox(
            'quizaccess_sebversion/enforcedefault',
            new lang_string('enforcedefault', 'quizaccess_sebversion'),
            new lang_string('enforcedefault_desc', 'quizaccess_sebversion'),
            '0',
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'quizaccess_sebversion/minversionmac',
            new lang_string('minversionmac', 'quizaccess_sebversion'),
            new lang_string('minversionmac_desc', 'quizaccess_sebversion'),
            '3.6.0',
            PARAM_RAW,
            10,
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'quizaccess_sebversion/minversionwin',
            new lang_string('minversionwin', 'quizaccess_sebversion'),
            new lang_string('minversionwin_desc', 'quizaccess_sebversion'),
            '3.10.0',
            PARAM_RAW,
            10
        )
    );
}
