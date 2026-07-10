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
 * Theme registration page.
 *
 * @package    theme_moove
 * @copyright  2025 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

require_login();
$context = \core\context\system::instance();
$PAGE->set_context($context);

require_capability('moodle/site:config', $context);

$url = new moodle_url('/theme/moove/register.php');

$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'theme_moove'));
$PAGE->set_heading(get_string('pluginname', 'theme_moove'));

$renderer = $PAGE->get_renderer('theme_moove');

$form = new \theme_moove\forms\register($url);

$redirecturl = new moodle_url('/admin/settings.php?section=themesettingmoove');

if ($form->is_cancelled()) {
    redirect($redirecturl);
}

if ($formdata = $form->get_data()) {
    $decoded = base64_decode($formdata->activationcode);
    $data = explode('|', $decoded);

    if ($data[0] != $CFG->wwwroot) {
        throw new \Exception('This site is not allowed to register this license key.');
    }

    $license = new \theme_moove\util\license();

    $license->force_activation($data[1], $data[2] ?? 0);

    purge_caches([
        'theme' => true,
        'other' => true,
    ]);

    $message = get_string_manager()->get_string('active_msg', 'theme_moove');

    redirect($redirecturl, $message, 0, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
