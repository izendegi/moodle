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
 * This is the file for manage groups form
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
class managegroups_form extends moodleform {
    /**
     * Add elements to form
     * @see $CFG
     * @see $DB
     * @see block_smowl_get_courses()
     */
    public function definition() {
        global $CFG, $DB, $COURSE;
        // Customdata.
        $id = $this->_customdata['id'];
        $availabilityconditionsjson = $this->_customdata['availabilityconditionsjson'];
        $mform = $this->_form;

        // Elements.
        $mform->addElement('hidden', 'id', $id);
        $mform->addElement('hidden', 'view', 'managegroups');
        // Types.
        $mform->setType('id', PARAM_INT);
        $mform->setType('view', PARAM_ALPHA);

        // Note: This field cannot be named 'availability' because that
        // conflicts with fields in existing modules (such as assign).
        // So it uses a long name that will not conflict.
        $mform->addElement('textarea', 'availabilityconditionsjson',
                get_string('groupsaccessrestrictions', 'block_smowl'));

        $mform->setDefault('availabilityconditionsjson', $availabilityconditionsjson);

        // The _cm variable may not be a proper cm_info, so get one from modinfo.
        require_once($CFG->dirroot.'/blocks/smowl/classes/availability_frontend.php');
        $af = new availability_frontend();
        $af->include_all_javascript($COURSE);

        $mform->addHelpButton('availabilityconditionsjson', 'availabilityconditionsjsonform', 'block_smowl');

        $this->add_action_buttons();

    }
}
