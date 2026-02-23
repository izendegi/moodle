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

namespace tiny_orphanedfiles;

use editor_tiny\plugin;
use editor_tiny\plugin_with_configuration;

/**
 * Tiny orphanedfiles plugin for Moodle.
 *
 * @package    tiny_orphanedfiles
 * @copyright  2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @copyright  2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @author     2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @author     2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugininfo extends plugin implements plugin_with_configuration {

    /**
     * Passes Information from php to javascript
     *
     * @param \context $context
     * @param array $options
     * @param array $fpoptions
     * @param \editor_tiny\editor|null $editor
     * @return array
     * @throws \dml_exception
     */
    public static function get_plugin_configuration_for_context(
        \context             $context,
        array                $options,
        array                $fpoptions,
        ?\editor_tiny\editor $editor = null): array {
        // Here we collect all php-information e.g. configs to be accessible in javascript.
        global $USER;
        $configuration = [];

        // Load user contextId from moodle and pass it to javascript.
        $usercontext = \context_user::instance($USER->id);
        $usercontextid = $usercontext->id;
        $configuration['userContextId'] = $usercontextid;

        // Websitesettings.
        $configuration['showreferencecountenabled'] = !!get_config('tiny_orphanedfiles', 'showreferencecountenabled');
        $configuration['orphanedfilescounteronly'] = !!get_config('tiny_orphanedfiles', 'orphanedfilescounteronly');
        return $configuration;
    }

    /**
     * Whether the plugin is enabled
     *
     * @param \context $context The context that the editor is used within
     * @param array $options The options passed in when requesting the editor
     * @param array $fpoptions The filepicker options passed in when requesting the editor
     * @param \editor_tiny\editor|null $editor The editor instance in which the plugin is initialised
     * @return bool
     * @throws \dml_exception
     */
    public static function is_enabled(
        \context             $context,
        array                $options,
        array                $fpoptions,
        ?\editor_tiny\editor $editor = null
    ): bool {
        $isenabled = (get_config('tiny_orphanedfiles', 'isactive') && has_capability('tiny/orphanedfiles:view', $context))
            ||
            (get_config('tiny_orphanedfiles', 'isactiveforsiteadmin') && is_siteadmin());
        return $isenabled;
    }

}
