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
 * Settings.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_structlabel\admin_setting_resourcesstyles;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'mod_structlabel/imagewidth',
        new lang_string('imagewidth', 'mod_structlabel'),
        new lang_string('imagewidth_desc', 'mod_structlabel'),
        150,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'mod_structlabel/imageheight',
        new lang_string('imageheight', 'mod_structlabel'),
        new lang_string('imageheight_desc', 'mod_structlabel'),
        150,
        PARAM_INT
    ));

    $settings->add(new admin_setting_resourcesstyles(
        'mod_structlabel/resourcesstyles',
        new lang_string('resourcesstyles', 'mod_structlabel'),
        new lang_string('resourcesstyles_desc', 'mod_structlabel')
    ));
}
