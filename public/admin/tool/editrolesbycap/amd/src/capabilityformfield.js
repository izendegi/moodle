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
 * AMD module to add filtering to the capability form field type.
 *
 * @module tool_editrolesbycap/capabilityformfield
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import * as Str from 'core/str';
import Templates from 'core/templates';
import {exception as displayException} from 'core/notification';

let delayHandle = -1;
const noneErrorMessageId = 'id_noneerrormessage';
/**
 * Render the search field element and initialize it's event handle.
 *
 * @param {String} selectorId
 */
export const initCapabilityFormField = async (selectorId) => {
    const select = document.getElementById(selectorId);
    if (!select) {
        return;
    }
    const searchFieldId = selectorId + 'capabilitysearch';
    const clearButtonid = selectorId + 'capabilityclear';
    const nonematchString = await Str.get_string('nonematch', 'tool_editrolesbycap');
    // Get any existing filter value.
    const filterKey = 'captblflt';
    const filterValue = sessionStorage.getItem(filterKey);
    const context = {
        id: searchFieldId,
        clearbuttonid: clearButtonid,
        value: filterValue,
    };

    Templates.renderForPromise('tool_editrolesbycap/filter_field', context).then(({html, js}) => {
        // Insert it into the container of the select.
        Templates.appendNodeContents(select.parentNode, html, js);
        const noneMessageE = document.createElement('optgroup');
        noneMessageE.setAttribute('label', nonematchString);
        noneMessageE.setAttribute('id', noneErrorMessageId);
        select.append(noneMessageE);
        setVisible(noneMessageE, false);

        // Wire the events so it actually does something.
        const searchField = document.getElementById(searchFieldId);
        const clearButton = document.getElementById(clearButtonid);

        searchField.addEventListener('input', change);
        searchField.filterKey = filterKey;
        searchField.select = select;

        clearButton.addEventListener('click', clear);
        clearButton.filterKey = filterKey;
        clearButton.select = select;
        clearButton.searchField = searchField;

        if (filterValue !== '') {
            filter(filterKey, select);
        }
    }).catch(displayException);

};

/**
 * Filters the capability selector
 *
 * @param {String} filterKey
 * @param {HTMLElement} select
 */
const filter = (filterKey, select) => {
    const clearButton = document.getElementById(select.getAttribute('id') + 'capabilityclear');
    const searchField = document.getElementById(select.getAttribute('id') + 'capabilitysearch');
    const filterText = searchField.value.toLowerCase();

    sessionStorage.setItem(filterKey, filterText);
    clearButton.disabled = (filterText === '');

    let allHidden = true;
    select.querySelectorAll('optgroup').forEach((optgroup) => {
        setVisible(optgroup, false);
        const lastGroup = optgroup;

        optgroup.querySelectorAll('option').forEach((option) => {
            const capName = option.textContent.toLowerCase();
            if (capName.indexOf(filterText) >= 0) {
                setVisible(lastGroup, true);
                setVisible(option, true);
                allHidden = false;
            } else {
                setVisible(option, false);
            }
        });
    });
    if (allHidden) {
        const noneMessageE = document.getElementById(noneErrorMessageId);
        setVisible(noneMessageE, true);
    }
};

/**
 * Clears the filter value.
 *
 * @param {Event} e the current event
 */
const clear = (e) => {
    e.target.searchField.value = '';
    if (delayHandle !== -1) {
        clearTimeout(delayHandle);
        delayHandle = -1;
    }
    filter(e.target.filterKey, e.target.select);
};

/**
 * Event callback for when the filter value changes
 *
 * @param {Event} e the current event
 */
const change = (e) => {
    let handle = setTimeout(function() {
        filter(e.target.filterKey, e.target.select);
    }, 100);
    if (delayHandle !== -1) {
        clearTimeout(delayHandle);
    }
    delayHandle = handle;
};

/**
 * Hide / Un-hide element.
 *
 * @param {Node} element
 * @param {Boolean} visible
 */
const setVisible = (element, visible) => {
    if (visible) {
        element.style.display = 'block';
    } else {
        element.style.display = 'none';
    }
};
