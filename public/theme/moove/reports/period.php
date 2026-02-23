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
 * @package    theme_moove
 * @copyright  2021 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../../config.php');

$license = new \theme_moove\util\license();

if (!$license->is_active()) {
    throw new \moodle_exception(get_string('invalid', 'theme_moove'));
}

require_login();

$startdate = optional_param('startdate', '', PARAM_TEXT);
$enddate = optional_param('enddate', '', PARAM_TEXT);

if (!$enddate) {
    $enddate = new \DateTime(date('Y-m-d'));
} else {
    $enddate = \DateTime::createFromFormat('Y-m-d', $enddate);
}

if (!$startdate) {
    $startdate = clone $enddate;

    $startdate->modify('first day of january this year');
} else {
    $startdate = \DateTime::createFromFormat('Y-m-d', $startdate);
}

$context = \core\context\system::instance();

require_capability('theme/moove:viewreports', $context);

$url = new moodle_url('/theme/moove/reports/period.php', [
    'startdate' => $startdate->format('Y-m-d'),
    'enddate' => $enddate->format('Y-m-d')
]);

$title = get_string('report_period', 'theme_moove');

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('theme_moove', 'reports');

$contentrenderable = new \theme_moove\output\reports\period($startdate, $enddate);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();