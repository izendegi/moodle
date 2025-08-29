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
 * Code fragment to define the version of etherpadlite.
 *
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @package    mod_etherpadlite
 * @author     Timo Welde <tjwelde@gmail.com>
 * @copyright  2012 Humboldt-UniversitÃ¤t zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2025041500;  // The current module version (Date: YYYYMMDDXX).
$plugin->release   = 'v5.0 (2025041500)';
$plugin->requires  = 2025040800;
$plugin->component = 'mod_etherpadlite';
$plugin->maturity  = MATURITY_STABLE;
