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
 * Typeform configuration form
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_typeform\typeform_api;

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/typeform/classes/typeform_api.php');

/**
 * Class mod_typeform_mod_form
 *
 * This class defines the settings form for the mod_typeform activity module in Moodle.
 * It extends the standard activity settings form (moodleform_mod) and provides
 * additional elements for the configuration of the Typeform module.
 */
class mod_typeform_mod_form extends moodleform_mod {
    /**
     * definition
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function definition() {
        global $CFG, $DB, $PAGE;
        $mform = $this->_form;

        $config = get_config('typeform');

        // -------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name', 'typeform'), ['size' => '48']);
        $mform->addHelpButton('name', 'name', 'typeform');
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // -------------------------------------------------------
        $mform->addElement('header', 'typeformsection', get_string('typeformsettings', 'typeform'));

        // Check if API token is configured.
        $apitoken = get_config('typeform', 'apitoken');
        if (empty($apitoken)) {
            $mform->addElement(
                'static',
                'notoken',
                '',
                get_string('notokenconfigured', 'typeform')
            );
        } else {
            // Workspace select.
            $allowedworkspaces = typeform_api::get_allowed_workspaces();
            $workspaces = [0 => 'Seleccionar un workspace...'];
            foreach ($allowedworkspaces as $allowedworkspace) {
                $workspaces[$allowedworkspace['id']] = $allowedworkspace['name'];
            }
            $mform->addElement(
                'select',
                'workspaceid',
                get_string('fworkspace', 'typeform'),
                $workspaces,
                ['id' => 'id_workspaceid_select']
            );
            $mform->setType('workspaceid', PARAM_RAW);
            $mform->addRule('workspaceid', null, 'required');

            // Load typeforms directly from API.
            $typeformoptions = [0 => get_string('selecttypeform', 'typeform')];
            if (!empty($this->_instance)) {
                $typeformactivitiy = $DB->get_record('typeform', ['id' => $this->_instance]);
                $forms = typeform_api::get_forms($typeformactivitiy->workspaceid);
                if (is_array($forms) && !empty($forms['forms'])) {
                    foreach ($forms['forms'] as $form) {
                        $typeformoptions[$form['id']] = $form['title'];
                    }
                }
            }

            // Typeform selection.
            $mform->addElement(
                'select',
                'idtypeformid',
                get_string('selecttypeform', 'typeform'),
                $typeformoptions,
                ['id' => 'id_typeformid_select']
            );
            $mform->addHelpButton('idtypeformid', 'selecttypeform', 'typeform');
            $mform->addRule('idtypeformid', null, 'required');
            $mform->setType('idtypeformid', PARAM_RAW);
            $mform->disabledIf('idtypeformid', 'workspaceid', 'eq', 0);
            if (!empty($this->_instance)) {
                $mform->setDefault('idtypeformid', $typeformactivitiy->typeformid);
            }
            $mform->getElement('idtypeformid')->setPersistantFreeze(false);
            $PAGE->requires->js_call_amd('mod_typeform/form_handler', 'init', [
                    'typeformid' => 'id_typeformid_select',
                    'workspaceid' => 'id_workspaceid_select',
            ]);

            // Hidden field to store typeform title.
            $mform->addElement('hidden', 'typeformtitle');
            $mform->setType('typeformtitle', PARAM_RAW);
            $mform->addElement('hidden', 'typeformid');
            $mform->setType('typeformid', PARAM_RAW);

            // Include anonymous Student Code option.
            $mform->addElement('advcheckbox', 'includestudentcode', get_string('includestudentcode', 'typeform'));
            $mform->addHelpButton('includestudentcode', 'includestudentcode', 'typeform');
            $mform->setDefault('includestudentcode', 0);
        }

        // -------------------------------------------------------
        $this->standard_coursemodule_elements();

        // -------------------------------------------------------
        $this->add_action_buttons();
    }

    /**
     * get_displayname
     * @param $form
     * @return string
     */
    private function get_displayname(array $form) {
        $displaytext = $form['title'];
        if (!empty($form['workspace'])) {
            $displaytext .= ' (' . $form['workspace'] . ')';
        }
        return $displaytext;
    }

    /**
     * validation
     * @param $data
     * @param $files
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['typeformid'])) {
            $errors['typeformid'] = get_string('errortypeformrequired', 'typeform');
        }

        return $errors;
    }

    /**
     * Add custom completion rules.
     *
     * @return array Array of string IDs of added items, empty array if none
     * @throws coding_exception
     */
    public function add_completion_rules(): array {
        $mform =& $this->_form;

        $mform->addElement(
            'advcheckbox',
            'completionsubmit',
            '',
            get_string('completionsubmit', 'mod_typeform')
        );
        // Enable this completion rule by default.
        $mform->setDefault('completionsubmit', 0);
        $mform->setType('completionsubmit', PARAM_INT);
        return ['completionsubmit'];
    }
    /**
     * Determines if completion is enabled for this module.
     *
     * @param array $data
     * @return bool
     */
    public function completion_rule_enabled($data): bool {
        return !empty($data['completionsubmit']);
    }
}
