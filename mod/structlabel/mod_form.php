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
 * Module form.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/structlabel/lib.php');

/**
 * Module form.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_structlabel_mod_form extends moodleform_mod {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        global $PAGE;
        $mform = $this->_form;
        $PAGE->force_settings_menu();

        $mform->addElement('header', 'generalhdr', get_string('general'));

        $mform->addElement('text', 'name', get_string('title', 'mod_structlabel'), ['maxlength' => 255]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('content', 'mod_structlabel'));
        $mform->addRule('introeditor', null, 'required', null, 'client');

        $fmoptions = mod_structlabel_image_filemanager_options();
        $mform->addElement('filemanager', 'image', get_string('image', 'mod_structlabel'), null, $fmoptions);
        $mform->addRule('image', null, 'required', null, 'client');

        $mform->addElement('header', 'linkshdr', get_string('supportingresources', 'mod_structlabel'));
        $mform->setExpanded('linkshdr');

        $linkels = [
            $mform->createElement('url', 'resourceurl', get_string('resourcenourl', 'mod_structlabel'),
                ['placeholder' => 'https://example.com'], ['usefilepicker' => false]),
            $mform->createElement('text', 'resourcetext', get_string('resourcetext', 'mod_structlabel'),
                ['placeholder' => 'Example Website']),
            $mform->createElement('text', 'resourceicon', get_string('resourceicon', 'mod_structlabel'),
                ['placeholder' => 'fa-link']),
        ];
        $linkopts = [
            'resourceurl' => [
                'type' => PARAM_URL
            ],
            'resourcetext' => [
                'type' => PARAM_TEXT,
            ],
            'resourceicon' => [
                'type' => PARAM_ALPHANUMEXT,
            ],
        ];
        $this->repeat_elements($linkels, 3, $linkopts, 'linkrepeattimes', 'linkaddmore', 3,
            get_string('addnmoreresources', 'mod_structlabel'), true);
        $this->init_iconpicker();

        $this->standard_coursemodule_elements();
        $this->add_action_buttons(true, false, null);
    }

    /**
     * Data pre-processing.
     *
     * @param array &$defaultvalues The default values.
     * @return void
     */
    public function data_preprocessing(&$defaultvalues) {
        global $DB;

        if ($this->current && !empty($this->current->instance)) {

            // Iniialise image draft area.
            $draftitemid = file_get_submitted_draft_itemid('image');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_structlabel', 'image', 0,
                mod_structlabel_image_filemanager_options());
            $defaultvalues['image'] = $draftitemid;

            // Restore the resources.
            $defaultvalues['resourceurl'] = [];
            $defaultvalues['resourcetext'] = [];
            $defaultvalues['resourceicon'] = [];
            $resources = $DB->get_records('structlabel_resources', ['structlabelid' => $this->current->instance], 'id');
            foreach ($resources as $resource) {
                $defaultvalues['resourceurl'][] = $resource->url;
                $defaultvalues['resourcetext'][] = $resource->text;
                $defaultvalues['resourceicon'][] = $resource->icon;
            }
        }
    }

    /**
     * Data post processing.
     *
     * @param stdClass $data The data.
     * @return void
     */
    public function data_postprocessing($data) {
        // Normalise the resources.
        $data->resources = array_filter(array_map(function($idx) use ($data) {
            $url = !empty($data->resourceurl[$idx]) ? $data->resourceurl[$idx] : '';
            $text = !empty($data->resourcetext[$idx]) ? $data->resourcetext[$idx] : '';
            $icon = !empty($data->resourceicon[$idx]) ? $data->resourceicon[$idx] : '';
            if (!$url || !$text) {
                return null;
            }
            return (object) ['url' => $url, 'text' => $text, 'icon' => $icon];
        }, array_keys($data->resourceurl)));
        unset($data->resourceurl, $data->resourcetext, $data->resourceicon);

        // Always mark as showing description on frontpage, this is mainly to return
        // the content to the mobile app hidden as a description. For the web, we do
        // observe this setting as we always display content on the course page.
        $data->showdescription = 1;
    }

    /**
     * Init icon picker.
     *
     * @return void
     */
    protected function init_iconpicker() {
        global $PAGE;
        $PAGE->requires->css(new moodle_url('/mod/structlabel/lib/fontawesome-iconpicker/fontawesome-iconpicker.min.css'));
        $PAGE->requires->js_call_amd('mod_structlabel/iconpicker', 'init', ['[id^=id_resourceicon_]']);
    }

    /**
     * Validation.
     *
     * @param array $data Array of data.
     * @param array $files The files.
     * @return array List of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (isset($data['resourceurl'])) {
            foreach ($data['resourceurl'] as $key => $url) {
                $hasurl = !empty($url);
                $hastext = !empty($data['resourcetext'][$key]);
                if ($hasurl xor $hastext) {
                    $errors["resourceurl[$key]"] = get_string('urlandtextrequired', 'mod_structlabel');

                } else if ($hasurl && !preg_match('@^https?://.+@', $url)) {
                    $errors["resourceurl[$key]"] = get_string('invalidurl', 'core_error');

                }
            }
        }

        return $errors;
    }

}
