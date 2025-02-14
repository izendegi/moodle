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
 * Renderer class UI.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package    block_user_favorites
 * @copyright  26-10-2018 MFreak.nl
 * @author     Luuk Verhoeven
 **/

use block_user_favorites\output\output_favorites;

/**
 * Class block_user_favorites_renderer
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  26-10-2018 MFreak.nl
 */
class block_user_favorites_renderer extends plugin_renderer_base {

    /**
     * Render favorites
     *
     * @param output_favorites $renderable
     *
     * @return string
     * @throws moodle_exception
     */
    public function render_favorites(output_favorites $renderable): string {
        $data = $renderable->export_for_template($this);

        return $this->render_from_template('block_user_favorites/favorites', $data);
    }

}
