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
 * tiny_fontsize for Moodle.
 *
 * @module      tiny_fontsize
 * @copyright   2026 Mikko Haiku <mikko.haiku@iki.fi>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getPluginOptionName} from 'editor_tiny/options';
import {pluginName} from './common';

const fontsizes = getPluginOptionName(pluginName, 'fontsizes');
const fontsizeunit = getPluginOptionName(pluginName, 'fontsizeunit');

/**
 * Register the options for the Tiny Font size plugin.
 *
 * @param {TinyMCE} editor
 */
export const register = (editor) => {

    editor.options.register(fontsizes, {
        processor: 'Array',
        "default": [],
    });

    editor.options.register(fontsizeunit, {
        processor: 'string',
        "default": 'pt',
    });

};

/**
 * Get the list of font sizes.
 *
 * @param {TinyMCE.editor} editor
 * @returns {Array} Array.
 */
export const getFontSizeList = (editor) => editor.options.get(fontsizes);

/**
 * Get the configured font size unit.
 *
 * @param {TinyMCE.editor} editor
 * @returns {string} The CSS unit to apply to font sizes.
 */
export const getFontSizeUnit = (editor) => editor.options.get(fontsizeunit);
