<?php
// This file is part of Ranking block for Moodle - http://moodle.org/
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
global $ADMIN;
if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingmondragon', get_string('pluginname', 'theme_mondragon'));
    $page = new admin_settingpage('theme_mondragon_general', get_string('generalsettings', 'theme_boost'));
    $setting = new admin_setting_heading('theme_mondragon_scormview_header', get_string('scormviewconfig', 'theme_mondragon'), '');
    $page->add($setting);
    $setting = new admin_setting_configcheckbox('theme_mondragon/activescormview', get_string('activescormview', 'theme_mondragon'), get_string('activescormview_desc', 'theme_mondragon'), 1);
    $page->add($setting);
    $settings->add($page);
}