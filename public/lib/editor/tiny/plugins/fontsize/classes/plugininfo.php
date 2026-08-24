<?php
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
 * Tiny Font size plugin plugin for Moodle.
 *
 * @package     tiny_fontsize
 * @copyright   2023 Mikko Haiku <mikko.haiku@mediamaisteri.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_fontsize;

use context;
use editor_tiny\editor;
use editor_tiny\plugin;
use editor_tiny\plugin_with_buttons;
use editor_tiny\plugin_with_menuitems;
use editor_tiny\plugin_with_configuration;

/**
 * Plugininfo class.
 */
class plugininfo extends plugin implements plugin_with_buttons, plugin_with_configuration, plugin_with_menuitems {
    /**
     * Default font sizes, used whenever none have been configured.
     */
    private const DEFAULT_FONTSIZES = [10, 12, 14, 18];

    /**
     * Default font size unit, used whenever none has been configured.
     */
    private const DEFAULT_FONTSIZEUNIT = 'pt';

    /**
     * Get available buttons.
     *
     * @return array
     */
    public static function get_available_buttons(): array {
        return [
            'tiny_fontsize/plugin',
        ];
    }

    /**
     * Get available menuitems.
     *
     * @return array
     */
    public static function get_available_menuitems(): array {
        return [
            'tiny_fontsize/plugin',
        ];
    }

    /**
     * Get plugin configuration.
     *
     * @param context $context
     * @param array $options
     * @param array $fpoptions
     * @param editor|null $editor
     * @return array
     */
    public static function get_plugin_configuration_for_context(
        context $context,
        array $options,
        array $fpoptions,
        ?editor $editor = null
    ): array {
        $config = [];
        $rawsizes = get_config('tiny_fontsize', 'fontsizes');
        if ($rawsizes === false || trim($rawsizes) === '') {
            // The setting default hasn't been written to config yet (e.g. plugin was
            // updated but the site hasn't gone through an upgrade yet). Fall back to
            // the same default used in settings.php so the picker still works.
            $rawsizes = implode("\r\n", self::DEFAULT_FONTSIZES);
        }
        $sizes = preg_split('/\r\n|\r|\n/', $rawsizes);
        $config['fontsizes'] = array_values(array_filter(array_map('intval', $sizes)));
        $config['fontsizeunit'] = get_config('tiny_fontsize', 'fontsizeunit') ?: self::DEFAULT_FONTSIZEUNIT;
        return $config;
    }
}
