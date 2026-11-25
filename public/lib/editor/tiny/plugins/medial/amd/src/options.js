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
 * Options helper for the Moodle tiny_medial plugin.
 *
 * @module      plugintype_pluginname/options
 * @copyright   2023 MEDIAL, Tim Williams <tim@medial.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getPluginOptionName} from 'editor_tiny/options';
import {pluginName} from './common';

// Helper variables for the option names.
export const baseurlName = getPluginOptionName(pluginName, 'baseurl');
const ltiurlName = getPluginOptionName(pluginName, 'ltiurl');
const statusurlName = getPluginOptionName(pluginName, 'statusurl');
const useridName = getPluginOptionName(pluginName, 'userid');
const hideinsertName = getPluginOptionName(pluginName, 'hideinsert');
const insertdelayName = getPluginOptionName(pluginName, 'insertdelay');
const oauthConsumerKeyName = getPluginOptionName(pluginName, 'oauthConsumerKey');
const modtypeName = getPluginOptionName(pluginName, 'modtype');
const placeholderName = getPluginOptionName(pluginName, 'placeholder');
const playersizeurlName = getPluginOptionName(pluginName, 'playersizeurl');
const courseName = getPluginOptionName(pluginName, 'course');
const launchTypeName = getPluginOptionName(pluginName, 'launchType');
const viewLaunchTypeName = getPluginOptionName(pluginName, 'viewLaunchType');
const libLaunchTypeName = getPluginOptionName(pluginName, 'libLaunchType');
const embedOptName = getPluginOptionName(pluginName, 'embedopt');
const linkOnlyName = getPluginOptionName(pluginName, 'linkonly');
const bs5Name = getPluginOptionName(pluginName, 'bs5');

/**
 * Options registration function.
 *
 * @param {tinyMCE} editor
 */
export const register = (editor) => {
    const registerOption = editor.options.register;

    // For each option, register it with the editor.
    // Valid type are defined in https://www.tiny.cloud/docs/tinymce/6/apis/tinymce.editoroptions/
    registerOption(baseurlName, {
        processor: 'string',
    });
    registerOption(ltiurlName, {
        processor: 'string',
    });
    registerOption(statusurlName, {
        processor: 'string',
    });
    registerOption(useridName, {
        processor: 'number',
    });
    registerOption(hideinsertName, {
        processor: 'boolean',
    });
    registerOption(insertdelayName, {
        processor: 'number',
    });
    registerOption(oauthConsumerKeyName, {
        processor: 'string',
    });
    registerOption(modtypeName, {
        processor: 'string',
    });
    registerOption(placeholderName, {
        processor: 'boolean',
    });
    registerOption(playersizeurlName, {
        processor: 'string',
    });
    registerOption(courseName, {
        processor: 'number',
    });
    registerOption(launchTypeName, {
        processor: 'number',
    });
    registerOption(viewLaunchTypeName, {
        processor: 'number',
    });
    registerOption(libLaunchTypeName, {
        processor: 'number',
    });
    registerOption(embedOptName, {
        processor: 'boolean',
    });
    registerOption(linkOnlyName, {
        processor: 'boolean',
    });
    registerOption(bs5Name, {
        processor: 'boolean',
    });
};

// TMW Note, when importing always use {} around the name, eg "import {getLtiurl} from './options';" .
// If you don't, you'll get an error "Uncaught TypeError: _options.default is not a function".
// The plugin skeleton didn't do this for the sample parameter so it broke.

export const getBaseurl = (editor) => editor.options.get(baseurlName);

/**
 * Fetch the ltiurl value for this editor instance.
 *
 * @param {tinyMCE} editor The editor instance to fetch the value for
 * @returns {object} The value of the ltiurl option
 */

export const getLtiurl = (editor) => editor.options.get(ltiurlName);

export const getStatusUrl = (editor) => editor.options.get(statusurlName);

export const getUserId = (editor) => editor.options.get(useridName);

export const getHideInsert = (editor) => editor.options.get(hideinsertName);

export const getInsertDelay = (editor) => editor.options.get(insertdelayName);

export const getOauthConsumerKey = (editor) => editor.options.get(oauthConsumerKeyName);

export const getModType = (editor) => editor.options.get(modtypeName);

export const getPlaceholder = (editor) => editor.options.get(placeholderName);

export const getPlayerSizeUrl = (editor) => editor.options.get(playersizeurlName);

export const getCourse = (editor) => editor.options.get(courseName);

export const getLaunchType = (editor) => editor.options.get(launchTypeName);

export const getViewLaunchType = (editor) => editor.options.get(viewLaunchTypeName);

export const getLibLaunchType = (editor) => editor.options.get(libLaunchTypeName);

export const getEmbedOpt = (editor) => editor.options.get(embedOptName);

export const getLinkOnly = (editor) => editor.options.get(linkOnlyName);

export const getBs5 = (editor) => editor.options.get(bs5Name);
