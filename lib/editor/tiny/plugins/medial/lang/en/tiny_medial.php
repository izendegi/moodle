<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     tiny_medial
 * @category    string
 * @copyright   2023 MEDIAL, Tim Williams <tim@medial.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowfeedback'] = 'Allow the use in grading feedback text areas';
$string['allowfeedback_desc'] = 'Use of the MEDIAL Feedback plugin is prefered for grading feedback, but you can choose to enable the use of the TinyMCE button in the text area instead.';
$string['button_insertmedial'] = 'Insert MEDIAL Video';
$string['embedopt'] = 'Enable embedding options';
$string['embedopt_desc'] = 'Enable a choice of embed styles for videos (embedded iframe, popup with thumbnail, popup link). Requires the filter plugin to be installed.';
$string['helplinktext'] = 'Insert MEDIAL Video';
$string['hideinsert'] = 'Hide the Insert button';
$string['hideinsert_desc'] = 'Hides the insert button at the bottom of the insert video dialog. Note: The insert button will aways be shown regardless of the setting here if the "Video add dialog box close delay in seconds" property of the activity module (helixmedia | modal_delay) is set to a negative number.';
$string['iframe'] = 'Embedded Video';
$string['inserttype'] = 'Insert as';
$string['internal'] = 'Treat MEDIAL as an "internal" file';
$string['internal_desc'] = 'TinyMCE file insert options for internal and external files are sometimes hidden by Moodle. MEDIAL is defined as an external file, however there may be a small number of cases where MEDIAL is wanted in a location that only allows internal files. Switching this option on will allow MEDIAL to be used where only internal files are allowed.';
$string['link'] = 'Link + Popup Video';
$string['medial:addliblink'] = 'Create MEDIAL Library Link';
$string['medial:visible'] = 'User can add MEDIAL videos using tiny text editor';
$string['medial:visiblemodtype'] = 'User can add MEDIAL videos using tiny text editor if the module is enabled for this permission in the tiny MEDIAL plugin config';
$string['menuitem_insertmedial'] = 'Insert MEDIAL Video';
$string['menuitem_insertmediallib'] = 'Insert MEDIAL Library Button';
$string['modaltitle'] = 'Select MEDIAL Content';
$string['modtypedesc'] = 'The MEDIAL TinyMCE plugin has an alternative permission tiny/medial:visiblemodtype which can be used to override the default MEDIAL atto permission, primarily so that students can be given access to the MEDIAL button in selected module types. By default no modules are enabled for this permission, but you can enable them by entering a list of module names in this box. The list should have one module name on each line and must use the underlying moodle code for the module written in lowercase, eg for Forums, enter forum. All modules included here will have the MEDIAL video generated as a link rather than an iframe, since iframes typically do not work for student users. Leave this blank if you do not wish to enable this feature.';
$string['modtypetitle'] = 'Modules that use the alternate permission';
$string['newtab'] = 'Link + New Tab';
$string['placeholder'] = 'Use placeholder URL (requires MEDIAL filter)';
$string['placeholder_desc'] = 'Use a placeholder for the MEDIAL Launch URL instead of the actual URL. This avoids broken links if your site URL changes but <span style="font-weight:bold">requires the MEDIAL filter plugin to be installed and active</span> to work. Activating this setting will cause a link to be used in preference to an iframe in all cases for compatibiltiy with the editing mode, so the "Use a link to the MEDIAL resource" settings will be ignored. Also, since the filter does not act on the editor itself, the MEDIAL links will be broken when used in the TinyMCE editor and will only work on the final page.';
$string['pluginname'] = 'MEDIAL';
$string['privacy:metadata'] = 'MEDIAL Plugin does not store any personal data';
$string['showlibrary'] = 'Show MEDIAL Library';
$string['showvideo'] = 'View MEDIAL video';
$string['thumbnail'] = 'Thumbnail + Popup Video';
$string['uselinkdesc'] = 'Some activity module types will strip out the MEDIAL iframe created by the MEDIAL TinyMCE plugin even for users who have teacher permissions. To work around this the atto plugin can generate a normal link to the resource instead. Modules should be listed here in the same way as the alternate permission function. The Workshop and Forum activities are known to do this so are included by default. If you would like the links to show as embedded iframes regardless, please install the MEDIAL filter plugin. If the placeholder option above is on, a link will be always be used regardless of the modules specified here.';
$string['uselinktitle'] = 'Use a link to the MEDIAL resource';
