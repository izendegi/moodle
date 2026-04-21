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
 * Name filter class extending Keyword filter class.
 *
 * @module     qbank_filtername/datafilter/filtertypes/search
 * @author     Mateusz Grzeszczak <mateusz.grzeszczak@p.lodz.pl>
 * @author     Mateusz Walczak <mateusz.walczak@p.lodz.pl>
 * @copyright  2024 TUL E-Learning Center
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Keyword from 'core/datafilter/filtertypes/keyword';
import Templates from 'core/templates';
import {getString} from 'core/str';

export default class extends Keyword {

    SELECTORS = {
        filterText: 'input[name=filtername-filtertext]',
        caseSensitive: 'input[name=filtername-casesensitive]',
    };

    constructor(filterType, rootNode, initialValues, filterOptions = {filterText: true, caseSensitive: false}) {
        super(filterType, rootNode, initialValues);
        this.addCheckboxes(filterOptions.filterText, filterOptions.caseSensitive);
    }

    /**
     * Add filter options checkboxes.
     *
     * @param {Boolean} filterText
     * @param {Boolean} caseSensitive
     */
    async addCheckboxes(filterText = true, caseSensitive = false) {
        const filterValueNode = this.getFilterValueNode();
        const {html} = await Templates.renderForPromise('qbank_filtername/checkboxes', {
            filtertext: filterText,
            casesensitive: caseSensitive,
        });
        filterValueNode.insertAdjacentHTML('afterend', html);
    }

    /**
     * Get the placeholder to use when showing the value selector.
     *
     * @return {Promise} Resolving to a String
     */
    get placeholder() {
        return getString('writetext', 'qbank_filtername');
    }

    /**
     * Get current states of filter options.
     *
     * @return {Array} Filter options states array.
     */
    get filterOptions() {
        return [
            {
                name: 'filtertext',
                value: this.filterRoot.querySelector(this.SELECTORS.filterText).checked
            },
            {
                name: 'casesensitive',
                value: this.filterRoot.querySelector(this.SELECTORS.caseSensitive).checked
            }
        ];
    }
}
