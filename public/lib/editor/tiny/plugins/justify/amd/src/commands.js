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
 * Tiny justify commands for Moodle.
 *
 * Registers the alignjustify toolbar button and menu item.
 *
 * @module     tiny_justify/commands
 * @copyright  2026 CTE-ZL IFRN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';

export const COMMAND_ALIGNJUSTIFY = 'alignjustify';
export const MENU_ALIGN = 'tiny_justify_align';

// Icon SVG for justify button (matches TinyMCE's native alignment icon style).
const ICON_SVG = '<svg width="24" height="24">' +
    '<path d="M5 5h14c.6 0 1 .4 1 1s-.4 1-1 1H5a1 1 0 1 1 0-2Zm0 4h14c.6 0 1 .4 1 1s-.4 1-1 1H5a1 1 0 1 1 0-2Z' +
    'm0 4h14c.6 0 1 .4 1 1s-.4 1-1 1H5a1 1 0 0 1 0-2Zm0 4h14c.6 0 1 .4 1 1s-.4 1-1 1H5a1 1 0 0 1 0-2Z" fill-rule="evenodd"/>' +
    '</svg>';

const ICON_NAME = 'tiny_justify_icon';

/**
 * Set up the commands for this plugin.
 *
 * @returns {Function} Setup function for each TinyMCE editor instance.
 */
export const getSetup = async() => {
    const [tooltip, menutext, aligntext, alignleftText, aligncenterText, alignrightText] = await Promise.all([
        getString('alignjustify', 'tiny_justify'),
        getString('alignjustifymenu', 'tiny_justify'),
        getString('alignmenu', 'tiny_justify'),
        getString('alignleft', 'tiny_justify'),
        getString('aligncenter', 'tiny_justify'),
        getString('alignright', 'tiny_justify'),
    ]);
    const onAction = (editor) => () => editor.execCommand('JustifyFull');
    const onSetup = (editor) => (api) => {
        editor.on('NodeChange', () => {
            api.setActive(editor.queryCommandState('JustifyFull'));
        });
    };

    return (editor) => {
        // Register the custom icon.
        editor.ui.registry.addIcon(ICON_NAME, ICON_SVG);

        // Register a toggle button for the toolbar.
        editor.ui.registry.addToggleButton(COMMAND_ALIGNJUSTIFY, {
            tooltip,
            icon: ICON_NAME,
            onAction: onAction(editor),
            onSetup: onSetup(editor),
        });

        // Register toggle menu items for all alignment options.
        // TinyMCE only registers these as toolbar buttons, not menu items,
        // so we need to register them for our custom nested menu.
        editor.ui.registry.addToggleMenuItem('alignleft', {
            text: alignleftText,
            icon: 'align-left',
            onAction: () => editor.execCommand('JustifyLeft'),
            onSetup: (api) => {
                editor.on('NodeChange', () => {
                    api.setActive(editor.queryCommandState('JustifyLeft'));
                });
            },
        });
        editor.ui.registry.addToggleMenuItem('aligncenter', {
            text: aligncenterText,
            icon: 'align-center',
            onAction: () => editor.execCommand('JustifyCenter'),
            onSetup: (api) => {
                editor.on('NodeChange', () => {
                    api.setActive(editor.queryCommandState('JustifyCenter'));
                });
            },
        });
        editor.ui.registry.addToggleMenuItem('alignright', {
            text: alignrightText,
            icon: 'align-right',
            onAction: () => editor.execCommand('JustifyRight'),
            onSetup: (api) => {
                editor.on('NodeChange', () => {
                    api.setActive(editor.queryCommandState('JustifyRight'));
                });
            },
        });
        editor.ui.registry.addToggleMenuItem(COMMAND_ALIGNJUSTIFY, {
            text: menutext,
            icon: ICON_NAME,
            onAction: onAction(editor),
            onSetup: onSetup(editor),
        });

        // Register a custom nested menu item that replaces the built-in 'align' submenu
        // to include 'alignjustify' inside it.
        editor.ui.registry.addNestedMenuItem(MENU_ALIGN, {
            text: aligntext,
            getSubmenuItems: () => 'alignleft aligncenter alignright alignjustify',
        });
    };
};
