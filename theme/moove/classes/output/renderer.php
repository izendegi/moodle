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
 * Module renderer
 *
 * @package    theme_moove
 * @copyright  2022 Willian Mano {@link https://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_moove\output;

use plugin_renderer_base;

/**
 * Theme renderer
 *
 * @package    theme_moove
 * @copyright  2022 Willian Mano {@link https://conecti.me}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Defer the instance in index to template.
     *
     * @param \renderable $page
     *
     * @return bool|string
     *
     * @throws \moodle_exception
     */
    public function render_reportindex(\renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('theme_moove/reportindex', $data);
    }

    /**
     * Defer the instance in period to template.
     *
     * @param \renderable $page
     *
     * @return bool|string
     *
     * @throws \moodle_exception
     */
    public function render_reportperiod(\renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('theme_moove/reportperiod', $data);
    }
}
