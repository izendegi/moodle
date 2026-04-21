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
 * @author Joan Carbassa (IThinkUPC) &lt;joan.carbassa@ithinkupc.com&gt;
 * @author Yerai Rodríguez (IThinkUPC) &lt;yerai.rodriguez@ithinkupc.com&gt;
 * @author Marc Geremias (IThinkUPC) &lt;marc.geremias@ithinkupc.com&gt;
 * @author Miguel Gutiérrez (UPCnet) &lt;miguel.gutierrez.jariod@upcnet.es&gt;
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradeexport_groupfilter_xls;

/**
 * Class grade_export_form_test
 *
 * This class represents a unit test case for the grade_export_form class
 * in the gradeexport_groupfilter_txt plugin.
 *
 * @coversDefaultClass \gradeexport_groupfilter_txt\grade_export_form
 * @group gradeexport_groupfilter_txt
 */
final class grade_export_form_test extends \advanced_testcase {

    /**
     * Test form_constructor.
     *
     * @covers \gradeexport_groupfilter_xls\grade_export_form::definition
     */
    public function test_form_constructor(): void {
        $this->resetAfterTest(true);
        $formoptions = [
            'includeseparator' => true,
            'publishing' => true,
            'simpleui' => true,
            'multipledisplaytypes' => true,
        ];
        $mform = new grade_export_form(null, $formoptions);
        $this->assertNotEmpty($mform, 'userfields created in the form');
    }

}
