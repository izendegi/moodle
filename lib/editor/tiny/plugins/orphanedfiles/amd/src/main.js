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
 * Storage helper for the Moodle Tiny orphanedfiles plugin.
 *
 * @module     tiny_orphanedfiles/plugin
 * @copyright  2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @copyright  2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @author     2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @author     2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Config from 'core/config';
import OrphanedFilesManager from "./orphanedfilesmanager";
import {getContextId, getDraftItemId} from 'editor_tiny/options';
import {getShowReferencecountEnabled, getOrphanedFilesCounterOnly, getUserContextId} from "./options";

export const register = (editor) => {
    // Main.js ist defined to be registerd in plugin.js.

    // global variable to store orphanedfilesindicator
    let orphanedfilesmanager = null;

    editor.on('input', () => {
        orphanedfilesmanager.update();
    });

    editor.on('change', () => {
        orphanedfilesmanager.update();
    });

    editor.on('Undo', () => {
        orphanedfilesmanager.update();
    });

    editor.on('Redo', () => {
        orphanedfilesmanager.update();
    });

    editor.on('focus', () => {
        // Focus benötigt IMMER ein true, da es Situationen gibt, in denen sich über Medien verwalten etwas an der
        // Dateimenge geändert haben kann und dabei aber KEINE Change-Event sondern nur ein focus-Event ausgelöst wird,
        // wenn man zurück zum Editor wechseln und den Fokus in den Edit-Bereich setzt.
        orphanedfilesmanager.update(true);
    });

    editor.on('init', () => {
        const params = {
            // We collect all configurations and information we need later like contextId, ...
            wwwRoot: Config.wwwroot,
            contextId: getContextId(editor),
            draftItemId: getDraftItemId(editor),

            // Collect more settings defined in options.js eg userContextId from moodle plugininfo
            userContextId: getUserContextId(editor),
            showReferenceCountEnabled: getShowReferencecountEnabled(editor),
            orphanedFilesCounterOnly: getOrphanedFilesCounterOnly(editor),
        };

        orphanedfilesmanager = new OrphanedFilesManager(params, editor);
        orphanedfilesmanager.createOrphanedArea();
        orphanedfilesmanager.update(true);
    });
};