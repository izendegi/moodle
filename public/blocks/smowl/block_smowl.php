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
 * This file contains the SMOWL block information.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_smowl extends block_base {

    /**
     * Core function used to initialize the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_smowl');
    }

    /**
     * Allows the block to be added multiple times to a single page
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Core function, specifies where the block can be used.
     * @return array
     */
    public function applicable_formats() {
        return [
            'course-view' => true,
            'mod' => false,
            'all' => false,
            'site' => true,
        ];
    }

    /**
     * Used to generate the content for the block.
     * @return string
     */
    public function get_content() {
        global $CFG, $OUTPUT, $COURSE, $USER, $DB;
        require_once($CFG->dirroot . '/blocks/smowl/lib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        // Entity Settings setted.
        if (!block_smowl_check_entity()) {
            $this->content->text = $OUTPUT->notification(get_string('noticeemptysmowlconfig', 'block_smowl'), 'danger');
            return $this->content;
        }
        if ($COURSE->id == SITEID) {
            $canmanagebulkactions = block_smowl_user_can_managebulkactions($this->context);
            $settingbulk = get_config('block_smowl', 'bulkactions');

            if ($canmanagebulkactions) {
                if ($settingbulk) {
                    $title = get_string('bulkactions', 'block_smowl');
                    $this->content->text = html_writer::tag('b', $title);
                    $this->content->text .= block_smowl_admin_site_content();
                } else {
                    $this->content->text .= $OUTPUT->notification(get_string('noticeactivebulkactions', 'block_smowl'), 'danger');
                }
            }
            return $this->content;
        }

        $itemalert = [];
        $itemsteacher = [];
        $itemsstudent = [];
        $icons = [];

        $siteadmin = has_capability('moodle/site:config', context_system::instance());

        $manageactivities = has_capability('block/smowl:manageactivities', $this->context);
        $siteadmin = has_capability('moodle/site:config', context_system::instance());
        $alternativerole = block_smowl_user_in_alternative_role($this->context);

        $teachermanage = ($siteadmin && !$alternativerole) || $manageactivities;

        $coursegroups = groups_get_all_groups($COURSE->id);
        $viewmanagegroups = has_capability('block/smowl:managegroups', $this->context);

        // LTI.
        $lticonnection = block_smowl_lti_check();
        if (!$lticonnection && $teachermanage) {
            block_smowl_lti_create_tool();
            block_smowl_api_send_lti_ws_config();
            $ltideploymentid = get_config('block_smowl', 'ltideploymentid');
            $lticonnection = block_smowl_lti_check();
        }
        if (!$lticonnection) {
            $this->content->text = $OUTPUT->notification(get_string('noticeemptysmowlconfig', 'block_smowl'), 'danger');
            return $this->content;
        }
        // Check/create in course.
        $ltiincourse = block_smowl_lti_check_course($COURSE->id);
        if (!$ltiincourse  && $teachermanage) {
            $cmlti = block_smowl_lti_create_course_tool($COURSE->id);
            if ($cmlti) {
                $ltiincourse = $cmlti;
            }
        }
        $ltivisible = block_smowl_lti_check_visible();
        if (!$ltivisible) {
            $this->content->text = $OUTPUT->notification(get_string('noticeltinotvisible', 'block_smowl'), 'danger');
            return $this->content;
        }

        $roleids = array_keys(get_archetype_roles('student'));
        $student = false;
        foreach ($roleids as $roleid) {
            if (user_has_role_assignment($USER->id, $roleid, $this->context->id)) {
                $student = true;
                break;
            }
        }

        $inmodule = isset($this->page->cm->modname);
        $contextmodule = $this->page->cm;

        if ($inmodule) {
            $bi = block_smowl_check_course_block($COURSE->id);
            // Check course-block forced to viwed in all contexts.
            if (isset($bi->id) && $bi->id == $this->instance->id) {
                // No shows course block.
                return $this->content;
            }
            // Check groups block availability.
            if (isset($bi->configdata) && $bi->configdata != '') {
                $biconfigdata = unserialize(base64_decode($bi->configdata));
                $this->config = new stdClass();
                if (isset($biconfigdata->availabilityconditionsjson)) {
                    $this->config->availabilityconditionsjson = $biconfigdata->availabilityconditionsjson;
                }
            }
        }
        if (!$viewmanagegroups &&
                isset($this->config->availabilityconditionsjson) &&
                !empty($this->config->availabilityconditionsjson)) {

            $blockavailable = block_smowl_check_group_restrictions($this);
            if (!$blockavailable) {
                return;
            }
        }
        // End Check groups block availability.

        // View floating block.
        $floatingblock = get_config('block_smowl', 'floatblock');

        if ($inmodule) {
            // Check if attempt block exists and create.
            if (block_smowl_check_attempt_instance($this->instance->id)) {
                return $this->content;
            }

            $bi = block_smowl_check_course_block($COURSE->id);
            // Check course-block forced to viwed in all contexts.
            if (isset($bi->id) && $bi->id == $this->instance->id) {
                // No shows course block.
                return $this->content;
            }
        }

        // Check smowl logs vs smowl blocks instances and send information to SMOWL.
        if ($this->page->cm &&
                $this->page->cm->context->contextlevel == CONTEXT_MODULE &&
                ($teachermanage)) {

            $cmid = $this->page->cm->id;
            $parentcontextid = $this->instance->parentcontextid;
            $instanceid = $this->instance->id;

            if (block_smowl_check_new_block($cmid, $parentcontextid)) {
                // Send information to SMOWL.
                block_smowl_post_instances([0 => $cmid]);
                // Create instance event.

                $event = \block_smowl\event\instance_created::create_instance($parentcontextid, $instanceid);
                $event->trigger();
                $itemalert[] = $OUTPUT->notification(get_string('activityupdate', 'block_smowl'), 'alert alert-info');
            }
        }
        // End Check smowl logs vs smowl blocks instances and send information to SMOWL.

        // Manage Admins and teachers context to view.
        $params = ['id' => $COURSE->id];
        if ($teachermanage) {
            // LTI.
            if ($teachermanage &&  $lticonnection && $ltiincourse) { // Para que el notediting pueda verlo.
                $title = get_string('ltimanagesmowl', 'block_smowl');
                $text = get_string('teachercontent', 'block_smowl');
                $textbutton = get_string('teacherbutton', 'block_smowl');

                $params['view'] = 'ltimanagesmowl';
                $url = new moodle_url('/blocks/smowl/manage.php', $params);

                $renderer = $this->page->get_renderer('block_smowl');
                $teacherlink = $renderer->get_block_course_content($title, $text, $textbutton, $url);
                $this->content->text = $teacherlink;
            }
            // Manage groups.
            if ($viewmanagegroups && !empty($coursegroups) &&
                    $teachermanage) {
                $text = get_string('managegroups', 'block_smowl');
                $params['view'] = 'managegroups';
                $url = new moodle_url('/blocks/smowl/manage.php', $params);
                $itemsteacher[] = html_writer::link($url, $text);
            }
        }

        $alternativerole = block_smowl_user_in_alternative_role($this->context);

        $isActingAsStudent = false;

        if ($alternativerole) {
            $role = $DB->get_record('role', ['id' => $alternativerole]);

            if ($role->archetype === 'student') {
                $isActingAsStudent = true;
            }
        }

        if ($student || $isActingAsStudent) {
            // In module like a teacher or manager only affects if is active the accessrule.
            if ($inmodule) {
                // In module.
                $this->page->requires->js('/blocks/smowl/lib/iframe_student.js', 'footer');
                $renderer = $this->page->get_renderer('block_smowl');

                // Check roles to show recording or demo camera link.
                $iframecontent = $renderer->get_iframe_student($floatingblock);
                $accesscontrol = get_config('block_smowl', 'accesscontrol');
                if ($accesscontrol) {
                    $attempt = optional_param('attempt', 0, PARAM_INT);
                    if ($attempt == 0) {
                        require_once($CFG->dirroot . '/lib/formslib.php');
                        $mform = new MoodleQuickForm('control_access', 'POST', $this->page->url);
                        $mform->addElement('hidden', 'langacwaiting', get_string('acwaiting', 'block_smowl'));
                        $mform->addElement('hidden', 'langacaccess', get_string('acaccess', 'block_smowl'));
                        $mform->addElement('hidden', 'langacnotaccess', get_string('acnotaccess', 'block_smowl'));
                        $mform->display();
                        $this->page->requires->js('/blocks/smowl/lib/access_control.js');
                    }
                }
                if ($floatingblock) {
                    $this->page->requires->js_call_amd('block_smowl/float_block', 'init');
                    $itemalert[] = $OUTPUT->notification(get_string('activeinpopup', 'block_smowl'), 'info');
                    $this->content->text = $OUTPUT->list_block_contents($icons, $itemalert);
                    $this->content->footer = $renderer->content_block_popup($iframecontent);
                } else {
                    if (!empty($itemalert)) {
                        $this->content->text .= $OUTPUT->list_block_contents($icons, $itemalert);
                    }
                    $this->content->text .= $iframecontent;
                }
                return $this->content;
            } else if ($student && $lticonnection && $ltiincourse) {
                // In course.
                $title = get_string('ltistudentsmowl', 'block_smowl');
                $text = get_string('studentcontent', 'block_smowl');
                $textbutton = get_string('studentbutton', 'block_smowl');

                $url = new moodle_url('/blocks/smowl/student.php', $params);

                $renderer = $this->page->get_renderer('block_smowl');
                $teacherlink = $renderer->get_block_course_content($title, $text, $textbutton, $url);
                $this->content->text = $teacherlink;
            }
        }
        if (!empty($itemalert)) {
            $this->content->text .= $OUTPUT->list_block_contents($icons, $itemalert);
        }
        if (!empty($itemsteacher)) {
            $this->content->text .= $OUTPUT->list_block_contents($icons, $itemsteacher);
        }
        if (!empty($itemsstudent) && !empty($itemsteacher)) {
            $this->content->text .= '<br/>';
        }
        if (!empty($itemsstudent)) {
            $this->content->text .= $OUTPUT->list_block_contents($icons, $itemsstudent);
        }
        return $this->content;
    }

    /**
     * Do any additional initialization you may need at the time a new block instance is created
     * @return boolean
     */
    public function instance_create() {
        global $CFG, $DB;
        $id = $this->instance->id;
        $parentcontextid = $this->instance->parentcontextid;
        $contextlevel = $DB->get_field('context', 'contextlevel', ['id' => $parentcontextid]);
        if ($contextlevel == CONTEXT_MODULE) {
            // If we are in activity context.
            $sql = "SELECT cm.id
                FROM {block_instances} bi
                JOIN {context} c ON bi.parentcontextid = c.id
                JOIN {course_modules} cm ON c.instanceid = cm.id
                WHERE bi.id = :id";
            $cmid = $DB->get_record_sql($sql, ['id' => $id]);
            require_once($CFG->dirroot . '/blocks/smowl/lib.php');
            block_smowl_post_instances([$cmid->id]);
        }
        // Create instance event.
        $event = \block_smowl\event\instance_created::create_instance($parentcontextid, $id);
        $event->trigger();
        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    public function instance_copy($fromid) {
        global $CFG, $DB;
        // Currently only called from /my and cannot be instantiated in /my
        // When called from backup/moodle2/restore_steplib.php we will call SMOWL here.
        $id = $this->instance->id;
        $parentcontextid = $this->instance->parentcontextid;
        $contextlevel = $DB->get_field('context', 'contextlevel', ['id' => $parentcontextid]);
        if ($contextlevel == CONTEXT_MODULE) {
            // If we are in activity context.
            $sql = "SELECT cm.id
                      FROM {block_instances} bi
                      JOIN {context} c ON bi.parentcontextid = c.id
                      JOIN {course_modules} cm ON c.instanceid = cm.id
                     WHERE bi.id = :id";
            $cmid = $DB->get_record_sql($sql, ['id' => $id]);
            require_once($CFG->dirroot . '/blocks/smowl/lib.php');
            block_smowl_post_instances([$cmid->id]);
        }
        // Create instance event.
        $event = \block_smowl\event\instance_created::create_instance($parentcontextid, $id);
        $event->trigger();
        return true;
    }

    /**
     * Delete everything related to this instance if you have been using persistent storage other than the configdata field.
     * @return boolean
     */
    public function instance_delete() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/blocks/smowl/lib.php');
        $id = $this->instance->id;
        $parentcontextid = $this->instance->parentcontextid;
        $context = $DB->get_record('context', ['id' => $parentcontextid]);
        if ($context->contextlevel == CONTEXT_COURSE) {
            // Search for course blocks, remove them and send information to SMOWL.
            $blocks = block_smowl_get_instances($context->instanceid);
            foreach ($blocks as $module) {
                block_smowl_post_instances([], [$module->id => $module]);
                // Delete BI.
                block_smowl_delete_instance($module);
            }
            // Delete instance event.
            $event = \block_smowl\event\instance_deleted::delete_instance($parentcontextid, $id);
            $event->trigger();

            // Añadir eliminación de LTI tool in course.
            $ltitypeid = get_config('block_smowl', 'ltitypeid');
            $modulename = 'lti';

            $sql = "SELECT cm.*
                FROM {course_modules} cm
                JOIN {modules} md ON md.id = cm.module
                JOIN {lti} m ON m.id = cm.instance AND typeid = :ltitypeid
                WHERE md.name = :modulename
                AND cm.course = :courseid";
            $params = ['modulename' => $modulename, 'courseid' => $context->instanceid, 'ltitypeid' => $ltitypeid];

            $ltis = $DB->get_records_sql($sql, $params, IGNORE_MISSING);
            if ($ltis) {
                require_once("$CFG->dirroot/course/lib.php");
                foreach ($ltis as $lti) {
                    course_delete_module($lti->id);
                }
            }
        }

        if ($context->contextlevel == CONTEXT_MODULE) {
            // If we are in activity context.
            $sql = "SELECT cm.id ,bi.id AS blockinstance, c.id AS context, cm.id AS coursemodule,
                           cm.instance AS moduleinstance, cm.course, m.name
                      FROM {block_instances} bi
                      JOIN {context} c ON bi.parentcontextid = c.id
                      JOIN {course_modules} cm ON c.instanceid = cm.id
                      JOIN {modules} m ON m.id = cm.module
                     WHERE bi.id = :id";
            $module = $DB->get_record_sql($sql, ['id' => $id]);
            block_smowl_post_instances([], [$module->id => $module]);
            // Delete BI.
            block_smowl_delete_instance($module);
        }
        return true;
    }

}
