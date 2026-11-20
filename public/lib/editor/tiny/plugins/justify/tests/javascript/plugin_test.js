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
 * QUnit tests for the tiny_justify AMD modules.
 *
 * @module     tiny_justify/tests
 * @copyright  2026 CTE-ZL IFRN
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['tiny_justify/commands', 'tiny_justify/configuration'], (commands, configuration) => {

    QUnit.module('tiny_justify');

    QUnit.test('COMMAND_ALIGNJUSTIFY constant is defined', (assert) => {
        assert.strictEqual(commands.COMMAND_ALIGNJUSTIFY, 'alignjustify',
            'COMMAND_ALIGNJUSTIFY should equal "alignjustify"');
    });

    QUnit.test('configure returns toolbar with alignjustify button', (assert) => {
        const mockInstanceConfig = {
            toolbar: [
                {
                    name: 'content',
                    items: ['bold', 'italic'],
                },
            ],
        };

        // configure() merges our button into the toolbar.
        const result = configuration.configure(mockInstanceConfig);
        assert.ok(result.toolbar, 'configure() should return an object with a toolbar property');
    });

    QUnit.test('getSetup is a function', (assert) => {
        assert.strictEqual(typeof commands.getSetup, 'function',
            'getSetup should be a function');
    });
});
