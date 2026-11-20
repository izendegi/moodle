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
 * Tiny justify plugin for Moodle.
 *
 * @module     tiny_justify/plugin
 * @copyright  2026 CTE-ZL IFRN
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getTinyMCE} from 'editor_tiny/loader';
import * as Configuration from './configuration';
import {getSetup} from './commands';

const pluginName = 'tiny_justify/plugin';

export const pluginMetadata = {
    name: pluginName,
    version: '2026030309',
};

// eslint-disable-next-line no-async-promise-executor
export default new Promise(async(resolve) => {
    const [tinyMCE, setupCommands] = await Promise.all([getTinyMCE(), getSetup()]);

    tinyMCE.PluginManager.add(pluginName, (editor) => {
        setupCommands(editor);
        return pluginMetadata;
    });

    resolve([pluginName, Configuration]);
});
