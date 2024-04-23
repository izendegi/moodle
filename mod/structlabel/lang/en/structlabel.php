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
 * Language file.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addnmoreresources'] = 'Add {no} more resources';
$string['modulename'] = 'Structured label';
$string['modulenameplural'] = 'Structured labels';
$string['pluginname'] = 'Structured label';
$string['pluginadministration'] = 'Structured label administration';
$string['modulename_help'] = 'The Structured Label plugin is designed to allow teachers to add attractive content blocks to break up their pages without needing to understand HTML. When adding a structured label tutors are asked to add a title, content, and an image. Optionally, links to appear in buttons with Font Awesome icon picker can be added.';
$string['title'] = 'Title';
$string['image'] = 'Image';
$string['resourcetext'] = 'Name';
$string['resourceicon'] = 'Icon';
$string['content'] = 'Content';
$string['resourcenourl'] = 'Resource {no} URL';
$string['structlabel:addinstance'] = 'Add a structured label to the course page';
$string['structlabel:view'] = 'View structured label';
$string['supportingresources'] = 'Supporting resources';
$string['urlandtextrequired'] = 'Both the URL and name are required';
$string['privacy:metadata'] = 'The mod_structlabel plugin does not store user data.';
$string['imagewidth'] = 'Image width';
$string['imagewidth_desc'] = 'The image will be resized when its width exceeds this value. When both the width and height are defined, the image will be cropped to these exact dimensions.';
$string['imageheight'] = 'Image height';
$string['imageheight_desc'] = 'The image will be resized when its height exceeds this value. When both the width and height are defined, the image will be cropped to these exact dimensions.';
$string['errornotacolouratline'] = 'The colour at line {$a} is not valid.';
$string['errorinvalidiconnameatline'] = 'The icon name at line {$a} is not valid.';
$string['resourcesstyles'] = 'Resources styles';
$string['resourcesstyles_desc'] = 'Define the styles for resources matching certain URLs.

Each line must include at least a URL fragment to match against a resource, and their background colour.
Optionally, a third parameter can be included to specify the icon of the resource. These parameters
must be delimited by a `|`.

Example:

```
youtube.com|#FF0000|fa-play-circle
lynda.com|#F1B500
```
';
