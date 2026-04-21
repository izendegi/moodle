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

namespace report_fundae;
use coding_exception;
use context_course;
use context_system;
use moodleform;
defined('MOODLE_INTERNAL') || die();
/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . '/../locallib.php');

/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

class select_course_form extends moodleform {
    /**
     * @return void
     * @throws coding_exception
     */
    public function definition(): void {
        $courseid = required_param('courseid', PARAM_INT);
        $onlystudents = optional_param('onlystudents', 0, PARAM_INT);
        $coursecontext = context_course::instance($courseid);
        $mform = $this->_form;

        $mform->addElement('header', 'filteroptionshdr', get_string('filteroptions', 'report_fundae'));
        $mform->setExpanded('filteroptionshdr');

        $courseid = required_param('courseid', PARAM_INT);
        $context = context_system::instance();
        $limittoenrolled = !has_capability('report/fundae:manage', $context);
        $options = ['multiple' => false, 'includefrontpage' => false, 'exclude' => $courseid, 'limittoenrolled' => $limittoenrolled];
        $mform->addElement('course', 'courseid', get_string('course'), $options);
        $mform->setType('courseid', PARAM_INT);

        $usersincourse = $onlystudents === 0 ? get_enrolled_users($coursecontext, '', 0, 'u.*', 'firstname')
            : get_enrolled_users($coursecontext, 'mod/assign:exportownsubmission', 0, 'u.*', 'firstname');
        $usernames = [];
        $usernames[0] = get_string('allusers', 'report_fundae');
        foreach ($usersincourse as $userid => $user) {
            $usernames[$userid] = $user->firstname . ' ' . $user->lastname . ' - ' . $user->email;
        }
        $options = [
            'multiple' => false,
            'placeholder' => get_string('selectuser', 'report_fundae'),
            'includefrontpage' => false,
            'noselectionstring' => get_string('allusers', 'report_fundae'),
        ];
        $mform->addElement('autocomplete', 'userid', get_string('user'), $usernames, $options);
        $mform->setType('userid', PARAM_INT);
        $mform->setDefault('userid', optional_param('userid', 0, PARAM_INT));

        $label = get_string('resultsperpage', 'report_fundae');
        $perpageoptions = array_combine([10,25,40], [10,25,40]);
        $mform->addElement('select', 'perpage', $label, $perpageoptions);
        $mform->setType('perpage', PARAM_INT);

        $mform->setDisableShortforms();
        $this->add_action_buttons(false, get_string('applyfilters', 'report_fundae'));
    }
}
