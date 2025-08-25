<?php

namespace theme_moove\forms;

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Categories form.
 *
 * @package     theme_moove
 * @copyright   2024 Willian Mano {@link https://conecti.me}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class register extends \moodleform {
    /**
     * The form definition.
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'activationcode', get_string('activationcode', 'theme_moove'));
        $mform->addRule('activationcode', get_string('required'), 'required', null, 'client');
        $mform->addRule('activationcode', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->setType('activationcode', PARAM_TEXT);

        $this->add_action_buttons();
    }
}
