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
 * This is the file for search bulk activities form
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Moodleform is defined in formslib.php.
require_once($CFG->libdir . "/formslib.php");

/**
 * Class for the form of the course
 * @copyright Smiley Owl Tech S.L.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulkactive_form extends moodleform {
    /**
     * Add elements to form
     * @see $DB
     * @see html_writer::tag()
     * @see get_fast_modinfo()
     */
    public function definition() {
        global $DB, $CFG;

        // Customdata.
        $mform = $this->_form;
        $category = 0;
        $mod = 'quiz';
        $activityname = '';
        if (isset($this->_customdata['category']) &&
                isset($this->_customdata['mod']) &&
                isset($this->_customdata['activityname'])) {
            $category = $this->_customdata['category'];
            $mod = $this->_customdata['mod'];
            $activityname = $this->_customdata['activityname'];
        }

        // Elements.
        $mform->addElement('hidden', 'action', 'bulkactive');

        $mform = $this->_form;

        $mform->setType('action', PARAM_ALPHA);

        // Start form.
        // Select category.
        $catlist = core_course_category::make_categories_list();
        $catlist['0'] = get_string('allcategories', 'block_smowl');
        $selectcat = $mform->addElement('select', 'category', get_string('coursecategory', 'block_smowl'), $catlist);

        $mform->setDefault('category', $category);
        $mform->addHelpButton('category', 'coursecategory', 'block_smowl');

        // Select activity type.
        if (get_config('block_smowl', 'tracking')) {
            // Any type of activity.
            $modstr = get_string('activitytype', 'block_smowl');

            require_once($CFG->dirroot.'/course/lib.php');
            $typeoptions = get_module_types_names(true);

            $modtype = $mform->addElement('select', 'mod', $modstr, $typeoptions);
            $mform->addHelpButton('mod', 'activitytype', 'block_smowl');
            $modtype->setSelected($mod);
        } else {
            // Onlu quiz activities.
            $mform->addElement('hidden', 'mod', 'quiz');
            $mform->setType('mod', PARAM_ALPHA);
        }
        // Select name.
        $mform->addElement('text', 'activityname', get_string('activityname', 'block_smowl'), ['size' => '30']);
        $mform->addHelpButton('activityname', 'activityname', 'block_smowl');
        $mform->setDefault('activityname', $activityname);
        $mform->setType('activityname', PARAM_RAW_TRIMMED);

        $this->add_action_buttons($cancel = true, get_string('searchactivities', 'block_smowl'));

        if (isset($this->_customdata['category']) &&
                isset($this->_customdata['mod']) &&
                isset($this->_customdata['activityname'])) {
            // Title of search results.
            $mform->addElement('html', '<hr/><h2 class="bulkresultstitle">'.get_string('searchresults', 'block_smowl').'</h2>');
            // Discriminate by category.
            if ($category) { // Selected category and sub categories.
                if (isset($CFG->version) && (float)$CFG->version < 2018120300.00) {
                    // Moodle branch < 3.6.
                    require_once($CFG->libdir. '/coursecatlib.php');
                    $coursecat = \coursecat::get($category);
                } else {
                    // Moodle branch >= 3.6.
                    $coursecat = core_course_category::get($category);
                }
                $courses = $coursecat->get_courses(['recursive' => $coursecat->id]);
            } else {
                $sql = "category<>0";
                $courses = $DB->get_records_select('course', $sql, [], 'id', 'id, fullname');
            }
            if (empty($courses)) {
                $strnofind = get_string('notfound', 'block_smowl');
                $html = html_writer::tag('p', $strnofind, ['class' => 'smowlcheck']);
                $mform->addElement('html', $html);
                return;
            }

            // Search activities.
            $modules = block_smowl_bulkactive_search_modules($courses, $mod, $activityname);

            // Show activities to select.
            if (empty($modules)) {
                $strnofind = get_string('notfound', 'block_smowl');
                $html = html_writer::tag('p', $strnofind, ['class' => 'smowlcheck']);
                $mform->addElement('html', $html);
                return;
            }

            $titlenameset = false;
            $course = new stdClass();
            $course->id = 0;
            $courseinstances = [];

            $group = 0;
            foreach ($modules as $module) {
                if ($module->course != $course->id) {
                    $titlenameset = false;
                    $course = $courses[$module->course];
                    $courseinstances = block_smowl_get_instances($course->id);
                }
                if (!$titlenameset) {
                    $group++;
                    $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
                    $link = html_writer::link($courseurl, $course->fullname, ['class' => 'smowlcheck']);
                    $html = html_writer::tag('p', $link,  ['class' => 'smowlcheck']);
                    $mform->addElement('html', $html);
                    $titlenameset = true;
                    $this->add_checkbox_controller($group);
                }
                $checked = 0;
                if (in_array($module->id, array_keys($courseinstances))) {
                    $checked = 1;
                }
                $mform->addElement('advcheckbox', 'mod-'.$mod.'-'.$module->id.'-'.$course->id,
                    $module->name, null, ['group' => $group]);
                $mform->setDefault('mod-'.$mod.'-'.$module->id.'-'.$course->id, $checked);
            }

            $this->add_action_buttons(true, get_string('savechanges', 'block_smowl'));
        }
    }
}
