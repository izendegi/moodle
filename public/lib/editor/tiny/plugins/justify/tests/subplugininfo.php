<?php
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
 * Subplugin info and interface tests for tiny_justify.
 * Used by moodle-plugin-ci to verify plugin metadata.
 *
 * @package    tiny_justify
 * @copyright  2026 CTE-ZL IFRN
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_justify;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/editor/tiny/lib.php');

/**
 * Subplugin info test class for tiny_justify.
 */
class subplugininfo_test extends \advanced_testcase {
    /**
     * Verify the plugin class exists and implements required interfaces.
     */
    public function test_plugin_class_exists(): void {
        $this->assertTrue(
            class_exists('tiny_justify\plugininfo'),
            'Plugin class tiny_justify\plugininfo must exist.'
        );
    }

    /**
     * Verify the plugin implements plugin_with_buttons.
     */
    public function test_implements_plugin_with_buttons(): void {
        $this->assertInstanceOf(
            \editor_tiny\plugin_with_buttons::class,
            new \tiny_justify\plugininfo(),
            'tiny_justify\plugininfo must implement editor_tiny\plugin_with_buttons.'
        );
    }

    /**
     * Verify the plugin implements plugin_with_configuration.
     */
    public function test_implements_plugin_with_configuration(): void {
        $this->assertInstanceOf(
            \editor_tiny\plugin_with_configuration::class,
            new \tiny_justify\plugininfo(),
            'tiny_justify\plugininfo must implement editor_tiny\plugin_with_configuration.'
        );
    }

    /**
     * Verify get_available_buttons returns the expected button list.
     */
    public function test_get_available_buttons(): void {
        $buttons = \tiny_justify\plugininfo::get_available_buttons();
        $this->assertContains(
            'tiny_justify/alignjustify',
            $buttons,
            'get_available_buttons() must include "tiny_justify/alignjustify".'
        );
    }

    /**
     * Verify the plugin implements plugin_with_menuitems.
     */
    public function test_implements_plugin_with_menuitems(): void {
        $this->assertInstanceOf(
            \editor_tiny\plugin_with_menuitems::class,
            new \tiny_justify\plugininfo(),
            'tiny_justify\plugininfo must implement editor_tiny\plugin_with_menuitems.'
        );
    }

    /**
     * Verify get_available_menuitems returns the expected item list.
     */
    public function test_get_available_menuitems(): void {
        $items = \tiny_justify\plugininfo::get_available_menuitems();
        $this->assertContains(
            'tiny_justify/alignjustify',
            $items,
            'get_available_menuitems() must include "tiny_justify/alignjustify".'
        );
    }
}
