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
 * Tiny orphanedfiles plugin for Moodle.
 *
 * @module     tiny_orphanedfiles/plugin
 * @copyright  2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @copyright  2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @author     2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @author     2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getTinyMCE} from 'editor_tiny/loader';
import {getPluginMetadata} from 'editor_tiny/utils';

import {component, pluginName} from './common';
import * as Options from './options';
import * as main from './main';

// Setup the orphanedfiles Plugin.
export default new Promise((resolve) => {
    Promise.all([
        getTinyMCE(), getPluginMetadata(component, pluginName),
    ]).then(([tinyMCE, pluginMetadata]) => {
        // Note: The PluginManager.add function does not accept a Promise.
        // Any asynchronous code must be run before this point.
        tinyMCE.PluginManager.add(pluginName, (editor) => {
            // Register options.
            Options.register(editor);
            // The Plugin does not work in wiki because wiki stores files in the same draftarea for every editor.
            // So we have to check if the page is a wiki page and then do not use the plugin tiny orphanedfiles.
            if (!document.URL.includes('/mod/wiki/edit.php?pageid')) {
                // Register the orphanedfilesindicator.js
                main.register(editor);
            }
            return pluginMetadata;
        });
        resolve(pluginName);
        return null;
    }).catch(() => {
        // No tiny editor present
    });
});
