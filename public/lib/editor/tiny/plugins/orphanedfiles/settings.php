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
 * Tiny text editor orphanedfiles Plugin settings file.
 *
 * @package    tiny_orphanedfiles
 * @copyright  2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @copyright  2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @author     2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @author     2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox(
            'tiny_orphanedfiles/isactive',
            get_string('isactive', 'tiny_orphanedfiles'),
            get_string('isactive_desc', 'tiny_orphanedfiles'),
            1
    ));

    $settings->add(new admin_setting_configcheckbox(
            'tiny_orphanedfiles/isactiveforsiteadmin',
            get_string('isactiveforsiteadmin', 'tiny_orphanedfiles'),
            get_string('isactiveforsiteadmin_desc', 'tiny_orphanedfiles'),
            0
    ));

    $settings->add(
            new admin_setting_configcheckbox(
                    'tiny_orphanedfiles/showreferencecountenabled',
                    get_string('showreferencecountenabled', 'tiny_orphanedfiles'),
                    get_string('showreferencecountenabled_desc', 'tiny_orphanedfiles'),
                    1
            )
    );
    $settings->add(
            new admin_setting_configcheckbox(
                    'tiny_orphanedfiles/orphanedfilescounteronly',
                    get_string('orphanedfilescounteronly', 'tiny_orphanedfiles'),
                    get_string('orphanedfilescounteronly_desc', 'tiny_orphanedfiles'),
                    0
            )
    );
}
