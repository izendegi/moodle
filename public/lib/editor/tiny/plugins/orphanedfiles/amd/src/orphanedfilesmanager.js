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
 * Storage helper for the Moodle Tiny orphanedfiles plugin.
 *
 * @module     tiny_orphanedfiles/plugin
 * @copyright  2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @copyright  2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @author     2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @author     2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Options from "./options";
import Templates from 'core/templates';
import {exception as displayException} from 'core/notification';
import {deleteDraftFiles, getAllDraftFiles} from "./repository";

/**
 * OrphanedfilesManager is created in main.js
 */
export default class OrphanedfilesManager {
    constructor(params, editor) {
        this.editor = editor;
        this.elementId = editor.id;
        this.editorContainer = editor.editorContainer;
        // Read from options.js
        this.draftItemId = params.draftItemId;
        // Read userContextId from moodle
        this.userContextId = params.userContextId;
        // Read websitesetting from options.js
        this.showReferenceCountEnabled = params.showReferenceCountEnabled;
        this.orphanedFilesCounterOnly = params.orphanedFilesCounterOnly;
        this.wwwRoot = params.wwwRoot;
        this.baseUrl = {};

        this.allFilesSet = new Set(); // Files
        this.usedFilesSet = new Set(); // Set of file strings(!)

        // Sets with filename strings
        this.oldUsedFileNamesInEditor = new Set();
        this.usedFilenamesInEditor = new Set();
        this.editorFilenamesHaveChanged = true;

        // Sets with file objects
        this.oldOrphanedFilesSet = new Set();
        this.orphanedFilesSet = new Set();
        this.orphanedFilesSetHaveChanged = false;
    }

    /**
     * Creates the wrapper for the Orphaned files area.
     * The wrapper is a `div`-Element with id js-orphaned-wrapper-[elementid]
     * and class js-orphaned-wrapper
     */
    createOrphanedArea() {
        this.orphanedArea = document.createElement('div');
        this.orphanedArea.id = 'tiny-js-orphaned-wrapper-' + this.elementId;
        this.orphanedArea.className = 'tiny-orphaned-js-orphaned-wrapper';
        this.orphanedArea.className = 'tiny-orphaned';
        this.editorContainer.after(this.orphanedArea);

        this.headerDiv = document.createElement('div');
        this.headerDiv.id = `has-orphaned-files-${this.elementId}`;
        this.headerDiv.classList.add(`hidden`);
        this.headerDiv.innerHTML = '⟳ ... LOAD "orphaned files indicator", 8, 1';
        this.orphanedArea.appendChild(this.headerDiv);

        this.bodyDiv = document.createElement('div');
        this.bodyDiv.id = `orphaned-files-${this.elementId}`;
        // Template will be inserted into this DOM element.
        this.bodyDiv.classList.add(`orphaned-files-content`);
        this.bodyDiv.classList.add(`orphaned-files-content-${this.elementId}`);
        this.bodyDiv.classList.add(`hidden`);
        this.orphanedArea.appendChild(this.bodyDiv);

    }

    /**
     * Updates the static allFilesSet. getAllDraftFiles creates AJAX-Calls do find the draft files
     *
     * @returns {*}
     */
    updateAllFiles() {
        return new Promise((resolve, reject) => {
            const draftItemId = Options.getDraftItemId(this.editor);
            getAllDraftFiles(draftItemId)
                .then(fileObject => {
                    const result = JSON.parse(fileObject.files);
                    this.allFilesSet = new Set([...result]); // Generate set from resultArray
                    resolve(result);
                    return null;
                }).catch(error => {
                reject(error); // Bei einem Fehler abgelehnt
            });
        });
    }

    /**
     * Returns the used Files as array. updateUsedFilenamesInEditor must be called before updateUsedFiles.
     * Update used Files is called *after* UpdateAllFiles
     *
     * @returns {array}
     */
    updateUsedFiles() {
        return new Promise((resolve) => {
            let i = 1;
            // Get *files* from filename and filepath strings in editor (by filtering allFilesSet
            for (const file of this.allFilesSet) {
                file.className = 'file-' + i;
                // Add individual information about the file e.g. dimensions, formated last modified date, ...
                if (file.image_width && file.image_height) {
                    file.dimensions = `${file.image_width}✕${file.image_height}`;
                } else {
                    file.dimensions = '';
                }
                const newDate = new Date(file.datemodified * 1000);
                const dateString = newDate.toLocaleString();
                file.datemodifiedFormated = dateString;

                if (this.usedFilenamesInEditor.has(file.filepath + file.filename)) {
                    this.usedFilesSet.add(file);
                }
                i = i + 1;
            }
            resolve(); // Erfolgreich aufgelöst
        });
    }

    /**
     * Updates the list of filenames used in the editor.
     *
     * This method scans the editor content for embedded file paths,
     * extracts the filenames, and stores them as a Set in `usedFilenamesInEditor`.
     * The search path is based on `wwwRoot`, `userContextId`, and `draftItemId`.
     *
     * It then checks whether the list of used files has changed since the last call.
     * If it has, `editorFilenamesHaveChanged` is set to `true`; otherwise, it's set to `false`.
     * The previous list is stored in `oldUsedFileNamesInEditor`.
     */
    updateUsedFilenamesInEditor() {
        const editorContent = this.editor.getContent();
        const baseUrl = `${this.wwwRoot}/draftfile.php/${this.userContextId}/user/draft/${this.draftItemId}/`;
        const pattern = new RegExp("[\"']" + baseUrl.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&') +
            "(?<filename>.+?)[\\?\"']", 'gm');
        // Get all used files in editor by searching editor content for filepatterns
        this.usedFilenamesInEditor = new Set([...editorContent.matchAll(pattern)].map((match) => '/' +
            decodeURIComponent(match.groups.filename)));
        if (this.setsAreEqual(this.usedFilenamesInEditor, this.oldUsedFileNamesInEditor)) {
            this.editorFilenamesHaveChanged = false;
        } else {
            this.editorFilenamesHaveChanged = true;
        }
        this.oldUsedFileNamesInEditor = this.usedFilenamesInEditor;
    }

    setsAreEqual(setA, setB) {
        if (setA.size !== setB.size) {
            return false;
        }
        for (let item of setA) {
            if (!setB.has(item)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Compares to allFiles and usedFiles to find all orphaned files
     *
     * @returns {*[]}
     */
    updateOrphanedFiles() {
        return new Promise((resolve) => {
            this.oldOrphanedFilesSet = this.orphanedFilesSet;
            this.orphanedFilesSet = new Set([...this.allFilesSet].filter(element => !this.usedFilesSet.has(element)));
            const setsAreEqual = this.setsAreEqual(this.orphanedFilesSet, this.oldOrphanedFilesSet);
            if (!setsAreEqual) {
                this.orphanedFilesSetHaveChanged = true;
            } else {
                this.orphanedFilesSetHaveChanged = false;
            }
            resolve();
        });
    }

    /**
     * Deletes the selected files.
     *
     * @param {array} files List of all selected files @returns {null}
     */
    deleteSelectedFiles(files) {
        const draftItemId = Options.getDraftItemId(this.editor);
        deleteDraftFiles(draftItemId, files).then(() => {
            this.update(true);
            return null;
        }).catch(() => {
            // No tiny editor present
        });
    }

    /**
     * Updates static usedFiles and orphanedFiles and call to renderBody if orphanedFiles list changes.
     *
     * @param {bool} forceChanged true if the user deleted a draft file with a click in trash icon. (needed for file-deletion)
     */
    update(forceChanged = false) {
        // Call updateUsedFilenamesInEditor to proof for changes in editor content
        this.updateUsedFilenamesInEditor();

        if (!this.editorFilenamesHaveChanged && !forceChanged) {
            return;
        }

        this.updateAllFiles().then(() => {
            return this.updateUsedFiles();
        }).then(() => {
            return this.updateOrphanedFiles();
        }).then(() => {
            if (this.orphanedFilesSetHaveChanged) {
                // Only render Body if orphaned files changed
                this.bodyDiv.classList.remove('hidden');
                this.renderBody();
            }
            return null;
        }).catch(() => {
            // No tiny editor present
        });
        this.orphanedFilesSetHaveChanged = false;
    }
    /**
     * Renders the list of orphaned files or in case of orphanedfilescounteronly renders just the number of orhaned files
     *
     * @returns {null}
     */
    renderBody() {
        const numberoforphanedfiles = this.orphanedFilesSet.size;
        if (numberoforphanedfiles !== 0) {
            var orphanedfilescounteronly = this.orphanedFilesCounterOnly;
            if (orphanedfilescounteronly) {
                const context = {
                    // Data to be rendered
                    numberoforphanedfiles: numberoforphanedfiles,
                };
                Templates.renderForPromise('tiny_orphanedfiles/orphanedfilescounteronly', context).then(({html, js}) => {
                    Templates.replaceNodeContents(`.orphaned-files-content-${this.elementId}`, html, js);
                    return null;
                }).catch(() => {
                    // No tiny editor present
                });
            } else {
                const websitesettings = Array();
                // Just for documentation purpose: We can access settings by two different ways.
                // We can access Options-Object or the data stored during construction.
                websitesettings.showreferencecountenabled = this.showReferenceCountEnabled;
                websitesettings.orphanedfilescounteronly = this.orphanedFilesCounterOnly;
                const context = {
                    // Data to be rendered
                    orphanedFiles: Array.from(this.orphanedFilesSet),
                    numberoforphanedfiles: numberoforphanedfiles,
                    websitesettings: websitesettings,
                    elementId: this.elementId
                };
                // Display Orphaned-Files-Table
                Templates.renderForPromise('tiny_orphanedfiles/orphanedfiles', context).then(({html, js}) => {
                    Templates.replaceNodeContents(`.orphaned-files-content-${this.elementId}`, html, js);
                    return null;
                }).then(() => {
                    // Add Listener to dynamic items in Orphaned-Files-Table e.g. Delete Buttons
                    return this.registerListener(Array.from(this.orphanedFilesSet));
                }).catch((error) => displayException(error));
            }
        } else {
            // In case of an undo the numberoforphanedfiles might get 0 during a table with orphand file is visible.
            // To be cheap in this case we do NOT remove the table but set table like in all other cases where
            // numberoforphanedfiles = 0 to hidden.
            this.bodyDiv.classList.add('hidden');
        }
        return null;
    }

    /**
     * Add Listener to dynamic items in Orphaned-Files-Table e.g. Delete Buttons
     * @param {array} files The list of files which are display in Orphaned Files Table.
     */
    registerListener(files) {
        // Add listener to the trash icons to be able to delete one single file.
        files.forEach((file) => {
            const deleteButton = document.querySelector(`#orphaned-files-${this.elementId} .orphaned-row.${file.className} span`);
            deleteButton.addEventListener("click", () => {
                const singleFile = [];
                singleFile.push({'filepath': file.filepath, 'filename': file.filename});
                // We can use deleteSelectedFiles() to delete a single file triggered by the trash icon click.
                this.deleteSelectedFiles(singleFile);
            });
        });

        // Add listener to the delete button to look for each selected file that will be deleted.
        let selectedFiles = [];
        const deleteSelectedButton = document.querySelector(`#orphaned-files-${this.elementId} button.deleteselected`);
        deleteSelectedButton.addEventListener("click", () => {
            for (const file of files) {
                const select = document.querySelector(`#orphaned-files-${this.elementId} .orphaned-row.${file.className} input`);
                if (select.checked) {
                    selectedFiles.push({'filepath': file.filepath, 'filename': file.filename});
                }
            }
            this.deleteSelectedFiles(selectedFiles);
        });
    }

}
