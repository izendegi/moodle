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
 * Resources styles admin setting.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_structlabel;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/structlabel/lib.php');

use invalid_parameter_exception;

/**
 * Resources styles admin setting.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_resourcesstyles extends \admin_setting_configtextarea {

    /**
     * Constructor.
     *
     * @param string $name The config name.
     * @param string $visiblename The human readable name.
     * @param string $desc The description.
     */
    public function __construct($name, $visiblename, $desc) {
        parent::__construct($name, $visiblename, $desc, '', PARAM_RAW);
    }

    /**
     * Validate.
     *
     * @param string $data The string.
     * @return bool|string
     */
    public function validate($data) {
        if (empty($data)) {
            return true;
        }

        $errors = [];
        $lines = mod_structlabel_parse_resource_styles($data);
        foreach ($lines as $i => $line) {
            if (empty($line)) {
                continue;
            }

            if (empty($line->colour) || !preg_match('/^#[a-f0-9]{3}([a-f0-9]{3})?$/i', $line->colour)) {
                $errors[] = get_string('errornotacolouratline', 'mod_structlabel', $i + 1);
            }

            if (!empty($line->icon)) {
                try {
                    validate_param($line->icon, PARAM_ALPHAEXT);
                } catch (invalid_parameter_exception $e) {
                    $errors[] = get_string('errorinvalidiconnameatline', 'mod_structlabel', $i + 1);
                }
            }
        }

        if (empty($errors)) {
            return true;
        }

        return implode(' ', $errors);
    }

}
