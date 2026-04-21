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
 * Commands helper for the Moodle tiny_medial plugin.
 *
 * @module      timy_medial/commands
 * @copyright   2023 MEDIAL, Tim Williams <tim@medial.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getButtonImage} from 'editor_tiny/utils';
import {get_string as getString} from 'core/str';
import {
    component,
    insertmedialButtonName,
    insertmedialMenuItemName,
    insertmediallibMenuItemName,
    icon,
} from './common';
import {handleAction, insertLibLink} from 'tiny_medial/ui';
import {getLibLaunchType} from './options';

/**
 * Get the setup function for the buttons.
 *
 * This is performed in an async function which ultimately returns the registration function as the
 * Tiny.AddOnManager.Add() function does not support async functions.
 *
 * @returns {function} The registration function to call within the Plugin.add function.
 */
export const getSetup = async() => {
    const [
        insertmedialButtonNameTitle,
        insertmedialMenuItemNameTitle,
        insertmediallibMenuItemNameTitle,
        buttonImage,
    ] = await Promise.all([
        getString('button_insertmedial', component),
        getString('menuitem_insertmedial', component),
        getString('menuitem_insertmediallib', component),
        getButtonImage('ed/medialadd', component),
    ]);

    return (editor) => {
        // Register the Moodle SVG as an icon suitable for use as a TinyMCE toolbar button.
        editor.ui.registry.addIcon(icon, buttonImage.html);

        // Register the insertmedial Toolbar Button.
        editor.ui.registry.addButton(insertmedialButtonName, {
            icon,
            tooltip: insertmedialButtonNameTitle,
            onAction: () => handleAction(editor),
        });

        // Add the insertmedial Menu Item.
        // This allows it to be added to a standard menu, or a context menu.
        editor.ui.registry.addMenuItem(insertmedialMenuItemName, {
            icon,
            text: insertmedialMenuItemNameTitle,
            onAction: () => handleAction(editor),
        });

        // Add the insertmedial Menu Item.
        // This allows it to be added to a standard menu, or a context menu.
        if (getLibLaunchType(editor) > -1) {
            editor.ui.registry.addMenuItem(insertmediallibMenuItemName, {
                icon,
                text: insertmediallibMenuItemNameTitle,
                onAction: () => insertLibLink(editor),
            });
        }
    };
};
