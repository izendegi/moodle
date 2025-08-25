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

// The max line length is set very low for nested code, so disable.
/* eslint-disable max-len */

/**
 * Link helper for Tiny Link plugin.
 *
 * @module      tiny_medial/link
 * @copyright   2023 Tim Williams, Streaming Ltd <tim@medial.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import Pending from 'core/pending';
import {getUserId, getOauthConsumerKey, getCourse, getPlayerSizeUrl, getBaseurl, getPlaceholder, getLinkOnly, getViewLaunchType} from './options';
//import Selectors from 'tiny_medial/selectors';

/**
 * Handle insertion of a new medial video
 *
 * @param {int} preid
 * @param {String} inserttype
 * @param {TinyMCE} editor
 */
export const setLink = (preid, inserttype, editor) => {

    var xmlDoc = new XMLHttpRequest();
    var params = "resource_link_id=" + preid + "&user_id=" + getUserId(editor) +
        "&oauth_consumer_key=" + getOauthConsumerKey(editor) +
        "&context_id="+ getCourse(editor) +
        "&include_height=Y";

    xmlDoc.onload = (response) => {
        const pendingPromise = new Pending('tiny_medial/setLink');
        checkResponse(preid, inserttype, editor, response).then(pendingPromise.resolve);
    };
    window.console.log(getPlayerSizeUrl(editor));
    xmlDoc.open("POST", getPlayerSizeUrl(editor) , true);
    xmlDoc.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlDoc.send(params);
};

/**
 * Next step adding the tag to the editor
 *
 * @param {int} preid
 * @param {String} inserttype
 * @param {TinyMCE} editor
 * @param {Object} response
 */
const checkResponse = async(preid, inserttype, editor, response) => {
    if (response.target.status < 200 && response.target.status >= 400) {
        return;
    }

    var resp = response.target.responseText.split(':');

    var audioonly = 0;
    if (resp.length == 3 && resp[2] == 'Y') {
        audioonly = 1;
    }
    window.console.log("audioonly="+audioonly);

    var template = "";
    if (getLinkOnly(editor) || inserttype != 'iframe') {
        template = 'tiny_medial/link';
    } else {
        template = 'tiny_medial/iframe';
    }

    setMedialLink(preid, inserttype, editor, audioonly, getViewLaunchType(editor), template);
};

/**
 * Final step adding the tag to the editor
 *
 * @param {int} preid
 * @param {String} inserttype
 * @param {TinyMCE} editor
 * @param {int} audioonly
 * @param {int} launchtype
 * @param {String} template
 */
export const setMedialLink = async(preid, inserttype, editor, audioonly, launchtype, template) => {

    var context = {url :
        getEmbedUrl(editor) + "?type="+launchtype+"&responsive=1&medialembed="+inserttype+"&audioonly="+audioonly+"&l=" + preid
    };

    if (audioonly == 1) {
        context.audioonly = true;
    }

    const {html} = await Templates.renderForPromise(template, context);
    window.console.log(html);
    editor.insertContent(html);
};

/**
 * Gets the base URL either with a placeholder of path at the front.
 * @param {TinyMCE} editor
 * @return url
 */
const getEmbedUrl = (editor) => {
    if (getPlaceholder(editor) == 1) {
        return "{{{medial_launch_base}}}/mod/helixmedia/launch.php";
    } else {
        return getBaseurl(editor) + "/mod/helixmedia/launch.php";
    }
};


