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
 * TinyMCE tiny_justify plugin.
 *
 * @package    tiny_justify
 * @copyright  2026 CTE-ZL IFRN
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_justify;

use context;
use editor_tiny\editor;
use editor_tiny\plugin;
use editor_tiny\plugin_with_buttons;
use editor_tiny\plugin_with_configuration;
use editor_tiny\plugin_with_menuitems;

/**
 * Tiny justify plugin for Moodle.
 */
class plugininfo extends plugin implements plugin_with_buttons, plugin_with_configuration, plugin_with_menuitems {
    /**
     * Whether the plugin is enabled for this context.
     *
     * @param context $context The context.
     * @param array $options Editor options.
     * @param array $fpoptions Filemanager/Filepicker options.
     * @param editor|null $editor The editor instance.
     * @return bool
     */
    public static function is_enabled(
        context $context,
        array $options,
        array $fpoptions,
        ?editor $editor = null
    ): bool {
        return true;
    }

    /**
     * Returns the buttons provided by this plugin.
     *
     * @return array
     */
    public static function get_available_buttons(): array {
        return [
            'tiny_justify/alignjustify',
        ];
    }

    /**
     * Returns the menu items provided by this plugin.
     *
     * @return array
     */
    public static function get_available_menuitems(): array {
        return [
            'tiny_justify/alignjustify',
        ];
    }

    /**
     * Returns plugin configuration for the given context.
     *
     * @param context $context The context.
     * @param array $options Editor options.
     * @param array $fpoptions Filemanager/Filepicker options.
     * @param editor|null $editor The editor instance.
     * @return array
     */
    public static function get_plugin_configuration_for_context(
        context $context,
        array $options,
        array $fpoptions,
        ?editor $editor = null
    ): array {
        return [];
    }
}
