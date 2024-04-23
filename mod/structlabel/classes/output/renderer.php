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
 * Renderer.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_structlabel\output;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/structlabel/lib.php');

use moodle_url;
use mod_structlabel\admin_setting_resourcesstyles;

/**
 * Renderer.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {

    /**
     * Render the content.
     *
     * @param stdClass $data The data to use when rendering.
     * @return string
     */
    public function display_content(\cm_info $cm) {
        $title = $cm->name;
        $resources = $this->style_resources($cm->customdata->resources);

        $imageurl = null;
        if (!empty($cm->customdata->image)) {
            // To prevent browser caching, we use the time modified and admin sizes in the path.
            $width = (int) get_config('mod_structlabel', 'imagewidth');
            $height = (int) get_config('mod_structlabel', 'imageheight');
            $timemodified = (int) $cm->customdata->image->timemodified;
            $imageurl = moodle_url::make_pluginfile_url($cm->context->id, 'mod_structlabel', 'image', 0,
                 "/{$timemodified}/{$width}x{$height}/", $cm->customdata->image->filename);
        }

        $data = [
            'title' => format_string($title),
            'content' => format_module_intro('structlabel', $cm->customdata, $cm->id),
            'hasresources' => !empty($resources),
            'resources' => $resources,
            'imageurl' => $imageurl
        ];

        return $this->render_from_template('mod_structlabel/content', $data);
    }

    /**
     * Style resources.
     *
     * @param array $resources The resources.
     * @return array
     */
    protected function style_resources($resources) {
        if (empty($resources)) {
            return $resources;
        }

        $styles = array_filter(
            mod_structlabel_parse_resource_styles(get_config('mod_structlabel', 'resourcesstyles')),
            function($line) {
                return !empty($line) && !empty($line->colour);
            }
        );

        if (empty($styles)) {
            return $resources;
        }

        foreach ($styles as $style) {
            $style->regex = '@' . preg_quote($style->url, '@') . '@i';
        }

        return array_map(function($resource) use ($styles) {
            foreach ($styles as $style) {
                if (preg_match($style->regex, $resource->url)) {
                    $resource->colour = $style->colour;
                    $resource->icon = !empty($style->icon) ? $style->icon : $resource->icon;
                    break;
                }
            }
            return $resource;
        }, $resources);

    }

}
