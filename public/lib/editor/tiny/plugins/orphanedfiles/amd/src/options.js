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
 * Options helper for the Moodle Tiny orphanedfiles plugin.
 *
 * @module     tiny_orphanedfiles/plugin
 * @copyright  2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @copyright  2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @author     2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @author     2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {pluginName} from './common';
import {getDraftItemId, getFilepickers, getPluginOptionName} from 'editor_tiny/options';
import {ensureEditorIsValid} from 'editor_tiny/utils';

// From editor_tiny/options.js
const initialisedOptionName = getPluginOptionName(pluginName, 'initialised');
// Load userContextId from plugininfo from moodle.
const userContextIdName = getPluginOptionName(pluginName, 'userContextId');

// From our options.js
// getPluginOptionName gets info from plugininfo.php
const showReferencecountEnabledName = getPluginOptionName(pluginName, 'showreferencecountenabled');
const orphanedFilesCounterOnlyName = getPluginOptionName(pluginName, 'orphanedfilescounteronly');

export const register = (editor) => {
    const registerOption = editor.options.register;

    // For each option, register it with the editor.
    // Valid type are defined in https://www.tiny.cloud/docs/tinymce/6/apis/tinymce.editoroptions/
    registerOption(userContextIdName, {
        processor: 'number',
        "default": -1,
    });

    // 0. In plugininfo.php Wert in php setzen
    // 1. in options.js registrieren
    // 2. in options.js exportieren
    // Now we can access data from php in javascript.
    registerOption(showReferencecountEnabledName, {
        processor: 'boolean',
        "default": false,
    });

    registerOption(orphanedFilesCounterOnlyName, {
        processor: 'boolean',
        "default": false,
    });

    registerOption(initialisedOptionName, {
        processor: 'boolean',
        "default": false,
    });

};

export const isInitialised = (editor) => {
    if (!ensureEditorIsValid(editor)) {
        return false;
    }

    return editor.options.get(initialisedOptionName);
};

export const getUserContextId = (editor) => editor.options.get(userContextIdName);
export const markInitialised = (editor) => editor.options.set(initialisedOptionName, true);
export const getShowReferencecountEnabled = (editor) => editor.options.get(showReferencecountEnabledName);
export const getOrphanedFilesCounterOnly = (editor) => editor.options.get(orphanedFilesCounterOnlyName);

// Export information that where included in import-statement.
export {
    getDraftItemId,
    getFilepickers,
};
