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
 * Strings for component 'block_completion_progress', language 'es'.
 *
 * Only the strings not yet available in AMOS. Moodle loads this file before the
 * 'es' language pack from AMOS, so the AMOS translations take precedence when they exist.
 *
 * @package    block_completion_progress
 * @copyright  2026 Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['config_showsinglestudentbar'] = 'Mostrar la barra del único estudiante';
$string['config_showsinglestudentbar_help'] = '<p>Cuando está marcada, si el curso tiene exactamente un estudiante (con matriculación activa y un rol basado en el arquetipo de estudiante), los usuarios que pueden acceder a la vista general de los estudiantes (por ejemplo, el profesorado) verán en la página del curso la barra de progreso de ese estudiante, con su nombre.</p><p>Quienes también vean su propia barra de progreso verán ambas, cada una con su etiqueta. Que un usuario vea su propia barra sigue dependiendo de la capacidad block/completion_progress:showbar.</p><p>Si no hay ningún estudiante o hay varios, no se muestra la barra del estudiante.</p>';
$string['ownprogress'] = 'Tu progreso';
$string['showsinglestudentbar'] = 'Mostrar por defecto la barra del único estudiante';
$string['showsinglestudentbar_desc'] = 'Valor por defecto del ajuste del bloque que muestra en la página del curso, a los usuarios que pueden acceder a la vista general de los estudiantes, la barra de progreso del único estudiante del curso (si hay exactamente uno). Cada instancia del bloque puede cambiarlo.';
$string['singlestudentprogress'] = 'Progreso de {$a}';
