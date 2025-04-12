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
 * output_favorites
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package    block_user_favorites
 * @copyright  26-10-2018 MFreak.nl
 * @author     Luuk Verhoeven
 **/

namespace block_user_favorites\output;

use block_user_favorites\favorites;
use dml_exception;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class output_favorites
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  26-10-2018 MFreak.nl
 */
class output_favorites implements renderable, templatable {

    /**
     * @var favorites $favorites Favorites
     */
    protected favorites $favorites;

    /**
     * @var string $currenturl Current URL
     */
    protected string $currenturl;

    /**
     * Admin catalog product output constructor.
     *
     * @param favorites $favorites
     * @param string $currenturl
     */
    public function __construct(favorites $favorites, string $currenturl = '') {
        $this->favorites = $favorites;
        $this->currenturl = $currenturl;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a mustache template.
     *
     * This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     *
     * @return stdClass
     * @throws dml_exception
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $PAGE;
        $data = [];
        $hascurrenturl = false;
        if ($this->favorites->has_favorites()) {

            $favorites = $this->favorites->get_all();
            foreach ($favorites as $favorite) {

                $favoriteurls = [
                    $favorite->url,
                    $favorite->url . 'index.php',
                ];

                if (in_array($this->currenturl, $favoriteurls, true)) {
                    $hascurrenturl = true;
                }

                $data[$favorite->hash] = [
                    'name' => $favorite->title,
                    'class' => (in_array($this->currenturl, $favoriteurls, true)) ? 'active' : '',
                    'url' => $favorite->url,
                    'hash' => $favorite->hash,
                    'sortorder' => $favorite->sortorder,
                ];
            }
        }

        return (object) [
            'data' => new \ArrayIterator($data),
            'has_favorites' => $this->favorites->has_favorites(),
            'hash' => md5($PAGE->url->out()),
            'hascurrenturl' => $hascurrenturl,
        ];
    }

}
