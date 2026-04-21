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
 * Tiny text editor orphanedfiles Plugin version file.
 *
 * @package    tiny_orphanedfiles
 * @copyright  2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @copyright  2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @author     2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @author     2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade steps.
 *
 * @param string $oldversion that is stored in the database
 * @return true
 * @throws coding_exception
 * @throws dml_exception
 * @throws downgrade_exception
 * @throws moodle_exception
 * @throws upgrade_exception
 */
function xmldb_tiny_orphanedfiles_upgrade($oldversion) {
    if ($oldversion < 2025052001) {
        // Set capability for manager role.
        $roleid = get_archetype_roles('manager')[0]->id ?? null;
        if ($roleid) {
            assign_capability('tiny/orphanedfiles:view',
                CAP_ALLOW,
                $roleid,
                context_system::instance()->id);
        }
        upgrade_plugin_savepoint(true, 2025052001, 'tiny', 'orphanedfiles');
    }
    if ($oldversion < 2025052800) {
        // Nothing to do because 2025052001 already sets the new capability.
        upgrade_plugin_savepoint(true, 2025052800, 'tiny', 'orphanedfiles');
    }
    return true;
}
