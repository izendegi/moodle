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
 * Commands helper for the Moodle tiny_embedquestion plugin.
 *
 * @module      tiny_embedquestion/commands
 * @copyright   2024 The Open University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getButtonImage} from 'editor_tiny/utils';
import {get_string as getString} from 'core/str';
import {
    component,
    buttonName,
    icon
} from './common';
import {DialogManager} from "./dialogue_manager";
import {ModalEmbedQuestionQuestionBank} from 'filter_embedquestion/modal_embedquestion_question_bank';
import * as Notification from 'core/notification';
const SELECTORS = {
    SWITCH_TO_OTHER_BANK: 'button[data-action="switch-question-bank"]',
};

let isEventsRegistered = false;

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
        buttonText,
        buttonImage,
    ] = await Promise.all([
        getString('pluginname', component),
        getButtonImage('icon', component),
    ]);

    return async(editor) => {
        registerManagerCommand(editor, buttonText, buttonImage);
    };
};

/**
 * Registers a custom command for embed question in the editor.
 *
 * @async
 * @param {Object} editor - The editor instance.
 * @param {string} buttonText - The text to display as a tooltip for the button.
 * @param {Object} buttonImage - The image to be displayed on the button.
 */
const registerManagerCommand = async(editor, buttonText, buttonImage) => {
    let currentDialog = null;
    const handleDialogManager = async() => {
        currentDialog = new DialogManager(editor);
        await currentDialog.displayDialogue('', false);
        currentDialog.currentModal.getModal().on('click', SELECTORS.SWITCH_TO_OTHER_BANK, showQuestionBankModal);
    };
    /*
    * Show the question bank modal when the user clicks on the switch to other bank button.
    * This will destroy the current modal and create a new one for the question bank.
    * @param {Event} e - The event triggered by the click.
    * @returns {Promise}
    */
    const showQuestionBankModal = async(e) => {
        e.preventDefault();
        const contextId = document.querySelector('input[name="contextid"]').value;
        const courseId = document.querySelector('input[name="courseid"]').value;
        const bankCmId = document.getElementById('id_qbankcmid').value;
        // Create a new instance of the modal to switch to the question bank.
        ModalEmbedQuestionQuestionBank.create({
            contextId,
            courseId,
            bankCmId,
            title: '',
            addOnPage: '',
            large: true,
            editor,
        }).catch(Notification.exception);
        if (currentDialog) {
            currentDialog.currentModal.destroy();
        }
    };


    // Just make sure we only register the events once.
    if (!isEventsRegistered) {
        isEventsRegistered = true;
        document.addEventListener('filter_embedquestion:qbank_selected', function(e) {
            currentDialog = new DialogManager(e.detail.editor);
            currentDialog.displayDialogue(e.detail.bankCmid, true).catch(Notification.exception).finally(() => {
                currentDialog.currentModal.getModal().on('click', SELECTORS.SWITCH_TO_OTHER_BANK, showQuestionBankModal);
            });
        });
    }

    editor.ui.registry.addIcon(icon, buttonImage.html);

    editor.ui.registry.addMenuItem(buttonName, {
        icon: icon,
        text: buttonText,
        onAction: async() => {
            await handleDialogManager();
        }
    });
};
