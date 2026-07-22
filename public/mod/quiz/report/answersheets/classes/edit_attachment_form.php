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
 * Edit essay attachment form.
 *
 * @package   quiz_answersheets
 * @copyright 2026 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_answersheets;

/**
 * The edit_attachment_form class.
 */
class edit_attachment_form extends \moodleform {
    #[\Override]
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement(
            'filemanager',
            'attachments',
            get_string('essay_attachment_label', 'quiz_answersheets'),
            null,
            $this->_customdata['filemanageroptions']
        );
        $mform->setDefault('attachments', []);
        $mform->addElement('hidden', 'attemptid', $this->_customdata['attemptid']);
        $mform->setType('attemptid', PARAM_INT);
        $mform->addElement('hidden', 'questionid', $this->_customdata['questionid']);
        $mform->setType('questionid', PARAM_INT);
        $mform->addElement('hidden', 'slot', $this->_customdata['slot']);
        $mform->setType('slot', PARAM_INT);
        $mform->addElement('hidden', 'returnurl', $this->_customdata['returnurl']);
        $mform->setType('returnurl', PARAM_URL);

        $this->add_action_buttons(true, get_string('saveonbehalfof', 'quiz_answersheets', $this->_customdata['studentname']));
    }
}
