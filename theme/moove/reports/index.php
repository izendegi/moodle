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
 * Eskada report index page
 *
 * @package    report_eskada
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');

$license = new \theme_moove\util\license();

if (!$license->is_active()) {
    throw new \moodle_exception(get_string('invalid', 'theme_moove'));
}

require_login();

$context = \core\context\system::instance();

require_capability('theme/moove:viewreports', $context);

$url = new moodle_url('/theme/moove/reportindex.php');

$title = get_string('report_graphs', 'theme_moove');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('theme_moove', 'reports');

$contentrenderable = new \theme_moove\output\reports\index();

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();