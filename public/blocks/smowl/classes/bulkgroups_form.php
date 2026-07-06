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
 * This is the file for manage bulk actions about groups form
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
 * Class for the form of the reports
 * @copyright Smiley Owl Tech S.L.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulkgroups_form extends moodleform {
    /**
     * Add elements to form
     * @see $CFG
     * @see $DB
     * @see block_smowl_get_courses()
     */
    public function definition() {
        global $DB, $CFG;

        // Customdata.
        $mform = $this->_form;
        $category = 0;
        $groupname = '';
        if (isset($this->_customdata['category']) && isset($this->_customdata['groupname'])) {
            $category = $this->_customdata['category'];
            $groupname = $this->_customdata['groupname'];
        }

        // Elements.
        $mform->addElement('hidden', 'action', 'bulkgroups');

        $mform = $this->_form;

        $mform->setType('action', PARAM_ALPHA);

        // Start form.
        // Select category.
        $catlist = core_course_category::make_categories_list();
        $catlist['0'] = get_string('allcategories', 'block_smowl');

        // Title of search results.
        $strcategory = get_string('coursecategory', 'block_smowl');
        $selectcat = $mform->addElement('select', 'category', $strcategory, $catlist);
        $mform->setDefault('category', $category);
        $mform->addHelpButton('category', 'coursecategory', 'block_smowl');

        // Select name.
        $mform->addElement('text', 'groupname', get_string('groupname', 'block_smowl'), ['size' => '30']);
        $mform->addHelpButton('groupname', 'groupname', 'block_smowl');
        $mform->setDefault('groupname', $groupname);
        $mform->setType('groupname', PARAM_RAW_TRIMMED);

        $this->add_action_buttons($cancel = true, get_string('searchgroups', 'block_smowl'));

        if (isset($this->_customdata['category']) && isset($this->_customdata['groupname'])) {
            // Title of search results.
            $strresults = get_string('searchresults', 'block_smowl');
            $mform->addElement('html', '<hr/><h2 class="bulkresultstitle">'.$strresults.'</h2>');
            $content = get_string('managebulkgroupsformintro', 'block_smowl').'<br/>';
            $content .= '<br/><b>'.get_string('managebulkgroupsformmoreinfo', 'block_smowl').'<br/>';
            $content .= get_string('managebulkgroupsforminfo', 'block_smowl').'</b><br/><br/>';

            $html = html_writer::tag('p', $content, ['class' => 'smowlcheckdescription']);
            $mform->addElement('html', $html);

            // Discriminate by category.
            if ($category) { // Selected category and sub categories.
                $coursecat = core_course_category::get($category);
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

            // Search groups.
            $groups = block_smowl_bulkgroups_search($courses, $groupname);
            // Show groups to select.
            $groupsform = [];
            foreach ($groups as $group) {
                if (in_array($group->name, $groupsform)) {
                    continue;
                }
                $groupsform[] = $group->name;
                $mform->addElement('advcheckbox', 'group-'.$group->id,
                    $group->name, null, ['group' => 1]);
            }
            if (!empty($groups)) {
                $this->add_checkbox_controller(1);
                $this->add_action_buttons(true, get_string('savechanges', 'block_smowl'));
            }
        }
    }
}
