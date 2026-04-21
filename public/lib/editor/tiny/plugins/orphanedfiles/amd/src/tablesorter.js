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
 * Tiny orphanedfiles plugin for Moodle.
 *
 * @module     tiny_orphanedfiles/plugin
 * @copyright  2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @copyright  2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @author     2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @author     2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @param {string} tableID
 */
export const init = (tableID) => {
   registerListeners(tableID);
};

/**
 * Registers the listeners for each header cell in the table.
 *
 * @param {string} tableID
 */
const registerListeners = (tableID) => {
   const table = document.getElementById(tableID);
   const headerCells = table.querySelectorAll('thead th');

   headerCells.forEach((cell, index) => {
      // Add listener for columns that where marked as sortable by data-attribute data-sortable = 'true'.
      const sortable = cell.getAttribute("data-sortable") || 'false';
      if (sortable === 'true') {
         cell.addEventListener('click', () => {
            sortTable(tableID, index);
         });
      }
   });
};

/**
 * Changes the sortorder of a table depending on a specific column.
 *
 * @param {string} tableID The id of the table which should be sorted.
 * @param {number} column The column by which should be sorted
 */
const sortTable = (tableID, column) => {
   const table = document.getElementById(tableID);
   const rows = Array.from(table.getElementsByTagName("tr"));
   rows.shift(); // Remove the header row from sorting
   const sortOrder = table.rows[0].cells[column].getAttribute("data-sort-order") || 'asc';

   rows.sort(function(a, b) {
      const textA = a.getElementsByTagName("td")[column].getAttribute('data-sort');
      const textB = b.getElementsByTagName("td")[column].getAttribute('data-sort');

      if (sortOrder === "desc") {
         if (!isNaN(textA) && !isNaN(textB)) {
            return Number(textA) - Number(textB);
         } else {
            return textA.localeCompare(textB);
         }
      } else {
         if (!isNaN(textA) && !isNaN(textB)) {
            return Number(textB) - Number(textA);
         } else {
            return textB.localeCompare(textA);
         }
      }
   });

   // Remove body of table to clean up the table.
   const tbody = table.getElementsByTagName("tbody")[0];

   // Remove all asc or desc in data-sort-order of each column so that only ONE column will be marked as sorted.
   for (let columnIndex = 0; columnIndex < table.rows[0].cells.length; columnIndex++) {
      const columnHeader = table.rows[0].cells[columnIndex];
      columnHeader.setAttribute("data-sort-order", "");
   }

   table.rows[0].cells[column].setAttribute("data-sort-order", sortOrder === 'asc' ? 'desc' : 'asc');
   tbody.innerHTML = "";

   // Insert sorted rows.
   rows.forEach(function(row) {
      tbody.appendChild(row);
   });
};
