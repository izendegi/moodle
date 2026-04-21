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

// Project implemented by the &quot;Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU&quot;.
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.
/**
 * Display information about all the gradeexport_groupfilter_xls modules in the requested course. *
 * @package gradeexport_groupfilter_xls
 * @copyright 2023 Proyecto UNIMOODLE
 * @author UNIMOODLE Group (Coordinator) &lt;direccion.area.estrategia.digital@uva.es&gt;
 * @author Miguel Gutiérrez (UPCnet) &lt;miguel.gutierrez.jariod@upcnet.es&gt;
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradeexport_groupfilter_xls;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/grade/export/lib.php');

/**
 * Class grade_export_groupfilter_xls
 *
 * This class extends the grade_export class and represents a grade export handler
 * specifically designed for group filtering and exporting data in text format.
 */
class grade_export_groupfilter_xls extends \grade_export {

    /**
     * The plugin type for this export handler.
     *
     * @var string
     */
    public $plugin = 'xls';

    /**
     * The validated form data.
     *
     * @var stdClass
     */
    private $formdata;

    /**
     * Constructor should set up all the private variables ready to be pulled
     *
     * @param object $course
     * @param int $groupid id of selected group, 0 means all
     * @param stdClass $formdata The validated data from the grade export form.
     */
    public function __construct($course, $groupid, $formdata) {
        parent::__construct($course, $groupid, $formdata);
        $this->formdata = $formdata;

        // Overrides.
        $this->usercustomfields = true;
    }

    /**
     * To be implemented by child classes
     */
    public function print_grades() {
        global $CFG;
        require_once($CFG->dirroot . '/lib/excellib.class.php');

        $exporttracking = $this->track_exports();

        $strgrades = get_string('grades');

        // If this file was requested from a form, then mark download as complete (before sending headers).
        \core_form\util::form_download_complete();

        // Calculate file name.
        $shortname = format_string(
            $this->course->shortname,
            true,
            ['context' => \context_course::instance($this->course->id)]
        );
        $downloadfilename = clean_filename("$shortname $strgrades.xls");
        // Creating a workbook.
        $workbook = new \MoodleExcelWorkbook("-");
        // Sending HTTP headers.
        $workbook->send($downloadfilename);
        // Adding the worksheet.
        $myxls = $workbook->add_worksheet($strgrades);

        // Added Group.
        $group = new \stdClass();
        $group->customid = 0;
        $group->shortname = "groups";
        $group->fullname = get_string('group', 'gradeexport_groupfilter_xls');

        // Print names of all the fields.
        $profilefields = \grade_helper::get_user_profile_fields($this->course->id, $this->usercustomfields);

        // Obtain selected fields from user formdata.
        $profilefieldsselected = [];
        foreach ($this->formdata as $key => $data) {
            if (mb_strpos($key, "userfieldsvisbile_") !== false) {
                $value = str_replace("userfieldsvisbile_", "", $key);
                array_push($profilefieldsselected, $value);
            }
        }

        // Edit fields only choosing the selected ones.
        foreach ($profilefields as $key => $fields) {
            if ($profilefieldsselected !== null && !in_array($fields->shortname, $profilefieldsselected)) {
                unset($profilefields[$key]);
            }
        }

        // Added groups field with the rest and obtain the total longitude.
        $size = array_push($profilefields, $group);
        // Move Group into the first position.
        for ($i = 0; $i < $size - 1; ++$i) {
            array_push($profilefields, array_shift($profilefields));
        }

        if (count($profilefields) != 1) {
            foreach ($profilefields as $id => $field) {
                $myxls->write_string(0, $id, $field->fullname);
            }
        } else {
            $myxls->write_string(0, 0, get_string('group', 'gradeexport_groupfilter_xls'));
        }

        $pos = count($profilefields);
        if (!$this->onlyactive) {
            $myxls->write_string(0, $pos++, get_string("suspended"));
        }
        foreach ($this->columns as $gradeitem) {
            foreach ($this->displaytype as $gradedisplayname => $gradedisplayconst) {
                $myxls->write_string(0, $pos++, $this->format_column_name($gradeitem, false, $gradedisplayname));
            }
            // Add a column_feedback column.
            if ($this->export_feedback) {
                $myxls->write_string(0, $pos++, $this->format_column_name($gradeitem, true));
            }
        }
        // Last downloaded column header.
        $myxls->write_string(0, $pos++, get_string('timeexported', 'gradeexport_groupfilter_xls'));

        // Print all the lines of data.
        $i = 0;
        $geub = new \grade_export_update_buffer();

        // Obtain list of the groups.
        $groups = groups_get_all_groups($this->course->id);
        $this->course->groupmode = VISIBLEGROUPS;
        // Obtain the groupid selected on visible groups filter.
        $groupid = groups_get_course_group($this->course, true);
        // Set all participants by default in case of not having groups.
        if (!$groupid) {
            $groupid = 0;
        }

        if (!empty($groups)) {
            // All participants format.
            if ($groupid == 0) {
                foreach ($groups as $usergroup) {
                    $gui = new \graded_users_iterator($this->course, $this->columns, $usergroup->id);
                    $gui->require_active_enrolment($this->onlyactive);
                    $gui->allow_user_custom_fields($this->usercustomfields);
                    $gui->init();
                    while ($userdata = $gui->next_user()) {
                        $i++;
                        $user = $userdata->user;
                        if (groups_is_member($usergroup->id, $user->id)) {

                            // Write user group on Groups column.
                            $myxls->write_string($i, 0, $usergroup->name);

                            foreach ($profilefields as $id => $field) {
                                // Remove group warning.
                                if ($field->shortname != "groups") {
                                    $fieldvalue = \grade_helper::get_user_field_value($user, $field);
                                    $myxls->write_string($i, $id, $fieldvalue);
                                }
                            }
                            $j = count($profilefields);
                            if (!$this->onlyactive) {
                                $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                                $myxls->write_string($i, $j++, $issuspended);
                            }
                            foreach ($userdata->grades as $itemid => $grade) {
                                if ($exporttracking) {
                                    $status = $geub->track($grade);
                                }
                                foreach ($this->displaytype as $gradedisplayconst) {
                                    $gradestr = $this->format_grade($grade, $gradedisplayconst);
                                    if (is_numeric($gradestr)) {
                                        $myxls->write_number($i, $j++, $gradestr);
                                    } else {
                                        $myxls->write_string($i, $j++, $gradestr);
                                    }
                                }
                                // Writing feedback if requested.
                                if ($this->export_feedback) {
                                    $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid], $grade));
                                }
                            }
                            // Time exported.
                            $myxls->write_string($i, $j++, time());
                        }
                    }
                }

                // Obtain users that has group (not considering all participants).
                $arraygroupsid = [];
                foreach ($groups as $group) {
                    $arraygroupsid[] += $group->id;
                }
                $usersingroup = groups_get_groups_members($arraygroupsid);

                $gui = new \graded_users_iterator($this->course, $this->columns, $this->groupid);
                $gui->require_active_enrolment($this->onlyactive);
                $gui->allow_user_custom_fields($this->usercustomfields);
                $gui->init();

                while ($userdata = $gui->next_user()) {
                    $i++;
                    $user = $userdata->user;

                    // Check user is from the group.
                    $exists = false;
                    foreach ($usersingroup as $usergroup) {
                        if ($usergroup->id == $user->id) {
                            $exists = true;
                        }
                    }
                    if (!$exists) {
                        // Write user group on Groups column.
                        $myxls->write_string($i, 0, '');

                        foreach ($profilefields as $id => $field) {
                            // Remove group warning.
                            if ($field->shortname != "groups") {
                                $fieldvalue = \grade_helper::get_user_field_value($user, $field);
                                $myxls->write_string($i, $id, $fieldvalue);
                            }
                        }
                        $j = count($profilefields);
                        if (!$this->onlyactive) {
                            $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                            $myxls->write_string($i, $j++, $issuspended);
                        }
                        foreach ($userdata->grades as $itemid => $grade) {
                            if ($exporttracking) {
                                $status = $geub->track($grade);
                            }
                            foreach ($this->displaytype as $gradedisplayconst) {
                                $gradestr = $this->format_grade($grade, $gradedisplayconst);
                                if (is_numeric($gradestr)) {
                                    $myxls->write_number($i, $j++, $gradestr);
                                } else {
                                    $myxls->write_string($i, $j++, $gradestr);
                                }
                            }
                            // Writing feedback if requested.
                            if ($this->export_feedback) {
                                $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid], $grade));
                            }
                        }
                        // Time exported.
                        $myxls->write_string($i, $j++, time());
                    } else {
                        $i--;
                    }
                }
            } else { // Group format.
                $gui = new \graded_users_iterator($this->course, $this->columns, $this->groupid);
                $gui->require_active_enrolment($this->onlyactive);
                $gui->allow_user_custom_fields($this->usercustomfields);
                $gui->init();
                while ($userdata = $gui->next_user()) {
                    $i++;
                    $user = $userdata->user;

                    // Check user is from the group.
                    if (groups_is_member($groupid, $user->id)) {
                        // Write user group on Groups column.
                        $myxls->write_string($i, 0, groups_get_group_name($groupid));

                        foreach ($profilefields as $id => $field) {
                            // Remove group warning.
                            if ($field->shortname != "groups") {
                                $fieldvalue = \grade_helper::get_user_field_value($user, $field);
                                $myxls->write_string($i, $id, $fieldvalue);
                            }
                        }
                        $j = count($profilefields);
                        if (!$this->onlyactive) {
                            $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                            $myxls->write_string($i, $j++, $issuspended);
                        }
                        foreach ($userdata->grades as $itemid => $grade) {
                            if ($exporttracking) {
                                $status = $geub->track($grade);
                            }
                            foreach ($this->displaytype as $gradedisplayconst) {
                                $gradestr = $this->format_grade($grade, $gradedisplayconst);
                                if (is_numeric($gradestr)) {
                                    $myxls->write_number($i, $j++, $gradestr);
                                } else {
                                    $myxls->write_string($i, $j++, $gradestr);
                                }
                            }
                            // Writing feedback if requested.
                            if ($this->export_feedback) {
                                $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid], $grade));
                            }
                        }
                        // Time exported.
                        $myxls->write_string($i, $j++, time());
                    } else {
                        $i--;
                    }
                }
            }
        } else {
            $gui = new \graded_users_iterator($this->course, $this->columns,  $this->groupid);
            $gui->require_active_enrolment($this->onlyactive);
            $gui->allow_user_custom_fields($this->usercustomfields);
            $gui->init();
            while ($userdata = $gui->next_user()) {
                $i++;
                $user = $userdata->user;

                // Write no Group for each user.
                $myxls->write_string($i, 0, "");

                foreach ($profilefields as $id => $field) {
                    // Remove group warning.
                    if ($field->shortname != "groups") {
                        $fieldvalue = \grade_helper::get_user_field_value($user, $field);
                        $myxls->write_string($i, $id, $fieldvalue);
                    }
                }
                $j = count($profilefields);
                if (!$this->onlyactive) {
                    $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                    $myxls->write_string($i, $j++, $issuspended);
                }
                foreach ($userdata->grades as $itemid => $grade) {
                    if ($exporttracking) {
                        $status = $geub->track($grade);
                    }
                    foreach ($this->displaytype as $gradedisplayconst) {
                        $gradestr = $this->format_grade($grade, $gradedisplayconst);
                        if (is_numeric($gradestr)) {
                            $myxls->write_number($i, $j++, $gradestr);
                        } else {
                            $myxls->write_string($i, $j++, $gradestr);
                        }
                    }
                    // Writing feedback if requested.
                    if ($this->export_feedback) {
                        $myxls->write_string($i, $j++, $this->format_feedback($userdata->feedbacks[$itemid], $grade));
                    }
                }
                // Time exported.
                $myxls->write_string($i, $j++, time());
            }
        }

        $gui->close();
        $geub->close();

        // Close the workbook.
        $workbook->close();

        exit;
    }
}
