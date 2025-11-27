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
 * Plugin administration pages are defined here.
 *
 * @package     tiny_medial
 * @category    admin
 * @copyright   2023 MEDIAL, Tim Williams <tim@medial.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/editor/tiny/plugins/medial/lib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage('tiny_medial_settings', new lang_string('pluginname', 'tiny_medial'));

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    if ($ADMIN->fulltree) {
        $cfg = tiny_medial_get_defaults();
        $options = [0 => new lang_string("no"), 1 => new lang_string("yes")];
        $hidesetting = new admin_setting_configselect(
            'tiny_medial/hideinsert',
            new lang_string('hideinsert', 'tiny_medial'),
            new lang_string('hideinsert_desc', 'tiny_medial'),
            $cfg->hideinsert,
            $options
        );
        $settings->add($hidesetting);

        $options = [0 => new lang_string("no")];
        if (tiny_medial_has_filter()) {
            $options[1] = new lang_string("yes");
        }

        $hidesetting = new admin_setting_configselect(
            'tiny_medial/placeholder',
            new lang_string('placeholder', 'tiny_medial'),
            new lang_string('placeholder_desc', 'tiny_medial'),
            $cfg->placeholder,
            $options
        );
        $settings->add($hidesetting);

        $settings->add(new admin_setting_configtextarea(
            'tiny_medial/modtypeperm',
            get_string("modtypetitle", "tiny_medial"),
            get_string("modtypedesc", "tiny_medial"),
            $cfg->modtypeperm,
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configtextarea(
            'tiny_medial/uselinkdesc',
            get_string("uselinktitle", "tiny_medial"),
            get_string("uselinkdesc", "tiny_medial"),
            $cfg->uselinkdesc,
            PARAM_TEXT
        ));

        $embedopt = new admin_setting_configselect(
            'tiny_medial/embedopt',
            new lang_string('embedopt', 'tiny_medial'),
            new lang_string('embedopt_desc', 'tiny_medial'),
            $cfg->embedopt,
            $options
        );
        $settings->add($embedopt);

        $feedback = new admin_setting_configselect(
            'tiny_medial/allowfeedback',
            new lang_string('allowfeedback', 'tiny_medial'),
            new lang_string('allowfeedback_desc', 'tiny_medial'),
            $cfg->allowfeedback,
            $options
        );

        $settings->add($feedback);

        $internal = new admin_setting_configselect(
            'tiny_medial/internal',
            new lang_string('internal', 'tiny_medial'),
            new lang_string('internal_desc', 'tiny_medial'),
            $cfg->placeholder,
            $options
        );
        $settings->add($internal);
    }
}
