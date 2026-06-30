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
 * Defines backup_typeform_activity_structure_step class
 *
 * @package     mod_typeform
 * @category    backup
 * @copyright   2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define the complete typeform structure for backup, with file and id annotations
 */
class backup_typeform_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the complete backup structure for the typeform module, including its
     * nested elements, data sources, and file or ID annotations.
     *
     * This method configures the root element for the typeform activity, specifies
     * its child elements, and outlines how data is sourced and associated with the
     * backup process. It supports inclusion or exclusion of user-specific data based
     * on the value of the 'userinfo' setting.
     *
     * @return backup_nested_element The root element of the typeform structure wrapped
     * into the standard activity structure.
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $typeform = new backup_nested_element(
            'typeform',
            ['id'],
            [
                    'name',
                    'intro',
                    'introformat',
                    'typeformid',
                    'workspaceid',
                    'typeformtitle',
                    'includestudentcode',
                    'completionsubmit',
                    'timemodified',
            ]
        );

        $typeformsubmissions = new backup_nested_element('submissions');
        $typeformsubmission = new backup_nested_element(
            'typeform_submission',
            ['id'],
            [
                    'typeform',
                    'userid',
                    'submitted',
                    'timemodified',
                    'timecreated',
                    'usermodified',
                ]
        );
        $typeform->add_child($typeformsubmissions);
        $typeformsubmissions->add_child($typeformsubmission);
        // Define sources.
        $typeform->set_source_table('typeform', ['id' => backup::VAR_ACTIVITYID]);
        if ($userinfo) {
            $typeformsubmission->set_source_table(
                'typeform_submission',
                ['typeform' => backup::VAR_PARENTID]
            );
        }

        // Define file annotations.
        $typeform->annotate_files('mod_typeform', 'intro', null);
        // This file area hasn't itemid.

        // Annotations for user ids in submissions.
        if ($userinfo) {
            $typeformsubmission->annotate_ids('user', 'userid');
            $typeformsubmission->annotate_ids('user', 'usermodified');
        }
        // Return the root element (typeform), wrapped into standard activity structure.
        return $this->prepare_activity_structure($typeform);
    }
}
