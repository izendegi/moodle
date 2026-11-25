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

require_once($CFG->dirroot.'/grade/export/grade_export_form.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

/**
 * Class grade_export_form
 *
 * This class represents a form for exporting grades.
 * It extends the \grade_export_form class.
 */
class grade_export_form extends \grade_export_form {

    /**
     * Define the form elements for grade_export_form
     */
    public function definition() {
        global $CFG;
        parent::definition();
        $element = $this->_form->createElement('header', 'userfieldsheader',
            get_string('userfieldsheader', 'gradeexport_groupfilter_xls'));
        $this->_form->insertElementBefore($element, 'submitbutton');

        // Obtain the user profield fields of the user from setting administration.
        $userprofieldfields = array_filter(explode(',', $CFG->grade_export_userprofilefields));

        // User base fields.
        $userdeafultfields = user_get_default_fields();

        $selecteduserprofieldfields = array_intersect($userprofieldfields, $userdeafultfields);

        // User profield fields.
        $userfieldsoptions = [];
        foreach ($selecteduserprofieldfields as $field) {
            $str = get_string($field);
            $userfieldsoptions[$field] = $str;
        }

        // User custom fields.
        $usercustomfields = [];
        foreach (profile_get_custom_fields() as $fielddata) {
            $usercustomfields[$fielddata->shortname] = $fielddata->name;
        }

        // User profield fields + User custom fields.
        $userfieldsoptions += $usercustomfields;

        $userfieldslabel = $this->_form->createElement('static', 'label_userfields',
            get_string('userfields_form', 'gradeexport_groupfilter_xls'));
        $this->_form->insertElementBefore($userfieldslabel, 'submitbutton');

        // User fields create form element.
        foreach ($userfieldsoptions as $key => $checkboxfields) {
            $elementname = 'userfieldsvisbile_'.$key;
            $foo = 'elem_'.$elementname;
            $$foo = $this->_form->createElement('checkbox', $elementname,  $checkboxfields);

            $this->_form->setDefault($elementname, 1);
            $this->_form->insertElementBefore($$foo, 'submitbutton');
        }
        $this->_form->closeHeaderBefore('userfieldsheader');

    }
}
