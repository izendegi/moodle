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
 * Tiny justify configuration for Moodle.
 *
 * Adds the alignjustify button to the toolbar and menu.
 *
 * @module     tiny_justify/configuration
 * @copyright  2026 CTE-ZL IFRN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {
    addQuickbarsToolbarItem,
    addToolbarButton,
} from 'editor_tiny/utils';
import {COMMAND_ALIGNJUSTIFY, MENU_ALIGN} from './commands';

/**
 * Configure the TinyMCE instance for this plugin.
 *
 * @param {object} instanceConfig The existing TinyMCE instance configuration.
 * @returns {object} The updated instance configuration.
 */
export const configure = (instanceConfig) => {
    // Replace the built-in 'align' submenu with our custom one that includes alignjustify.
    let menu = instanceConfig.menu;
    if (menu && menu.format && menu.format.items) {
        menu.format.items = menu.format.items.replace(/\balign(?!\w)/g, MENU_ALIGN);
    }

    return {
        toolbar: addToolbarButton(instanceConfig.toolbar, 'alignment', COMMAND_ALIGNJUSTIFY, 'alignright'),
        menu,
        quickbars_selection_toolbar: addQuickbarsToolbarItem(
            instanceConfig.quickbars_selection_toolbar,
            '|',
            'alignleft',
            'aligncenter',
            'alignright',
            COMMAND_ALIGNJUSTIFY,
        ),
    };
};
