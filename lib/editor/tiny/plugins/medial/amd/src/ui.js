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
 * Tiny Medial UI.
 *
 * @module      tiny_medial/ui
 * @copyright   2023 Tim Williams, Streaming Ltd <tim@medial.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import MedialModal from 'tiny_medial/modal';
import {getBaseurl, getLtiurl, getLaunchType, getLibLaunchType, getModType, getUserId, getOauthConsumerKey, getStatusUrl, getHideInsert, getEmbedOpt, getInsertDelay} from './options';
//import {getPermissions} from "tiny_link/options";
import {setLink, setMedialLink} from "tiny_medial/link";
import Selectors from 'tiny_medial/selectors';

let preid = -1;
let gotIn = false;

// TMW eslint insists that interval is unused, despite the fact that it is used in the code below, so disable the check.
// eslint-disable-next-line no-unused-vars
let interval = null;

let ed = null;
let modalPromises = false;

/**
 * Handle action.
 *
 * @param {TinyMCE} editor
 * @param {boolean} unlink
 */
export const handleAction = (editor, unlink = false) => {
    if (!unlink) {
        displayDialogue(editor);
    } else {
       // unSetLink(editor);
    }
};

/**
 * Display the link dialogue.
 *
 * @param {TinyMCE} editor
 * @returns {Promise<void>}
 */
const displayDialogue = async(editor) => {
    ed = editor;

    modalPromises = await ModalFactory.create({
        type: MedialModal.TYPE,
        templateContext: getTemplateContext(editor),
        large: true,
    });

    modalPromises.show();
    const $root = await modalPromises.getRoot();
    const root = $root[0];
    //const currentForm = root.querySelector('form');

    $root.on(ModalEvents.hidden, () => {
        window.removeEventListener("message", receiveMessage);
        modalPromises.destroy();
        modalPromises = false;
    });

    // Don't wait for the shown event, it often triggers too late.
    //$root.on(ModalEvents.shown, () => {
    //console.log("Add message listener");
    window.addEventListener("message", receiveMessage, false);
    //});

    root.addEventListener('click', (e) => {
        const submitAction = e.target.closest(Selectors.actions.submit);
        if (submitAction) {
            e.preventDefault();
            setLink(preid, getLinkType(editor), editor);
            modalPromises.destroy();
        }

    });
};

const getLinkType = (editor) => {
    return document.getElementById('medial_insert_type_'+editor.id).value;
};

/**
 * Get template context.
 *
 * @param {TinyMCE} editor
 * @returns {Object}
 */
const getTemplateContext = (editor) => {
    return Object.assign({}, {
        elementid: editor.id,
        edit: true,
        medialurl: getLtiurl(editor),
        launchurl: getBaseurl(editor)+"/mod/helixmedia/launch.php?type="+getLaunchType(editor)+"&modtype="+getModType(editor),
        hideinsert: getHideInsert(editor),
        embedopt: getEmbedOpt(editor)
    }, {});
};

/**
* Listener for the message that tells us the resource link ID
* @param {Event} event The message event object
*/
export const receiveMessage = (event) => {
/* eslint-disable no-console */
    //console.log("recieveMessage");
    //console.log(event.data);
    //console.log(typeof event.data);

    if (typeof event.data === 'string') {


        var i = event.data.indexOf("preid_");
    //console.log("i="+i);
        if (i == 0) {
//console.log("start checkStatus");
            preid = event.data.substring(6);
            interval = setTimeout(checkStatus, 5000);
        }
    }
/* eslint-enable no-console */
};

/**
 * Monitors the status of the video selection on the HML server
 * Note, this doesn't use setInterval so that this check will quickly die if there is a problem
 * rather than continuing for ever. The check is a convenience and isn't critical to the operation
 * of the plugin.
**/

const checkStatus = () => {
    var xmlDoc = new XMLHttpRequest();
    var params = "resource_link_id=" + preid + "&user_id=" + getUserId(ed) +
        "&oauth_consumer_key=" + getOauthConsumerKey(ed);

    xmlDoc.onload = (response) => {

/* eslint-disable no-console */
    //console.log("status (gotin:"+gotIn+")");
    //console.log(response.target.status+" "+response.target.responseText);
/* eslint-enable no-console */

        if (!modalPromises) {
            gotIn = false;
            return;
        }

        if (response.target.status < 200 && response.target.status >= 400) {
            // Something went wrong. Show the user the insert button just in case and give up on the monitoring.
            setInsertDisplay('inline');
            return;
        }

        if (response.target.responseText == "IN") {
            gotIn = true;
            setInsertDisplay('none');
        }

        if (response.target.responseText == "OUT" && gotIn == true) {
            gotIn = false;
            if (!getHideInsert(ed)) {
                setInsertDisplay('inline');
            }

            var delay = getInsertDelay(ed);
            if (delay > -1) {
                if (delay == 0) {
                    setLink(preid, getLinkType(ed), ed);
                    modalPromises.destroy();
                } else {
                    setTimeout( () => { setLink(preid, getLinkType(ed), ed); modalPromises.destroy();}, delay * 1000);
                }
            }
        } else {
            interval = setTimeout(checkStatus, 2000);
        }
    };

    xmlDoc.open("POST", getStatusUrl(ed) , true);
    xmlDoc.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlDoc.send(params);
};

const setInsertDisplay = (state) => {
    var e = document.getElementById('mod_helixmedia_launchframebutton_'+ed.id);
    if (typeof e != 'undefined') {
        e.style.display = state;
    }
};

/**
 * Handle insertion of a new medial video
 *
 * @param {TinyMCE} editor
 */
export const insertLibLink = (editor) => {
//window.console.log('insertLibLink');
//window.console.log(editor);
    setMedialLink(0, 'library', editor, 0, getLibLaunchType(editor), 'tiny_medial/library');
};
