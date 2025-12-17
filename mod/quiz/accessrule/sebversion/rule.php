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

use mod_quiz\quiz_settings;
use mod_quiz\local\access_rule_base;

/**
 * Rule definition class for the quizaccess_sebversion plugin.
 *
 * @package   quizaccess_sebversion
 * @copyright 2025, Philipp Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_sebversion extends access_rule_base {
    /** @var string */
    const DEFAULT_MIN_VERSION_WIN = '3.10.0';

    /** @var string */
    const DEFAULT_MIN_VERSION_MAC = '3.6.0';

    #[\Override]
    public static function make(quiz_settings $quizobj, $timenow, $canignoretimelimits) {
        if (!self::must_activate($quizobj)) {
            return null;
        }

        return new self($quizobj, $timenow);
    }

    /**
     * Check whether the access rule should be activated or not, based on the user, their
     * capabilities and the quiz settings.
     *
     * @param quiz_settings $quizobj the quiz
     * @return bool
     */
    protected static function must_activate(quiz_settings $quizobj): bool {
        // If the quiz settings do not require enforcing a SEB version, leave.
        $quiz = $quizobj->get_quiz();
        if (empty($quiz->sebversion_enforce)) {
            return false;
        }

        // Do not enforce SEB versions for users that may preview the quiz.
        if ($quizobj->is_preview_user()) {
            return false;
        }

        // Also, do not enforce SEB versions for users that may bypass SEB altogether.
        $context = \context_module::instance($quizobj->get_cmid());
        if (has_capability('quizaccess/seb:bypassseb', $context)) {
            return false;
        }

        // Do not enforce SEB versions if the quiz does not require SEB, unless we are running
        // in a Behat test.
        if (!$quiz->seb_requiresafeexambrowser) {
            return defined('BEHAT_SITE_RUNNING');
        }

        // In all other cases, enforce the version.
        return true;
    }

    #[\Override]
    public function setup_attempt_page($page) {
        // Reviews can be done without the Safe Exam Browser, so leave.
        if ($page->pagetype === 'mod-quiz-review') {
            return;
        }
        // Our main work is done client-side, so let's initialize our module.
        $page->requires->js_call_amd(
            'quizaccess_sebversion/overlay',
            'init',
            [
                'minVersionWin' => get_config('quizaccess_sebversion', 'minversionwin') ?: self::DEFAULT_MIN_VERSION_WIN,
                'minVersionMac' => get_config('quizaccess_sebversion', 'minversionmac') ?: self::DEFAULT_MIN_VERSION_MAC,
                'behat' => defined('BEHAT_SITE_RUNNING'),
            ],
        );
    }

    #[\Override]
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        // For consistency with the other SEB options, we use a Yes/No dropdown rather than a checkbox.
        $element = $mform->createElement(
            'selectyesno',
            'sebversion_enforce',
            get_string('sebversion_enforce', 'quizaccess_sebversion')
        );
        $mform->setDefault('sebversion_enforce', get_config('quizaccess_sebversion', 'enforcedefault'));
        $mform->hideIf('sebversion_enforce', 'seb_requiresafeexambrowser', 'eq', '0');

        // Our option should be at the end of the SEB section, i. e. just before the "Extra restrictions" section.
        $mform->insertElementBefore($element, 'security');

        // For the help text, we need the currently configured minimum versions.
        $a = (object)[
            'mac' => get_config('quizaccess_sebversion', 'minversionmac') ?: self::DEFAULT_MIN_VERSION_MAC,
            'win' => get_config('quizaccess_sebversion', 'minversionwin') ?: self::DEFAULT_MIN_VERSION_WIN,
        ];
        $mform->addHelpButton('sebversion_enforce', 'sebversion_enforce', 'quizaccess_sebversion', '', false, $a);
    }

    #[\Override]
    public static function save_settings($quiz) {
        global $DB;

        // Check if there are already settings for this quiz. If there are, we update them.
        // Otherwise, we create a new record.
        $record = $DB->get_record('quizaccess_sebversion', ['quizid' => $quiz->id]);
        if ($record) {
            $record->enforceversion = $quiz->sebversion_enforce;
            $result = $DB->update_record('quizaccess_sebversion', $record);
        } else {
            // Check the system preferences to see whether our plugin should, by default, be active
            // for a new quiz.
            $defaultsetting = get_config('quizaccess_sebversion', 'enforcedefault') ?: '0';
            // Use the setting from the form. If it is not there (which should not happen), then use the
            // default setting from the system preferences.
            $record = ['quizid' => $quiz->id, 'enforceversion' => $quiz->sebversion_enforce ?? $defaultsetting];
            $id = $DB->insert_record('quizaccess_sebversion', $record);
        }
    }

    #[\Override]
    public static function delete_settings($quiz) {
        global $DB;
        $result = $DB->delete_records('quizaccess_sebversion', ['quizid' => $quiz->id]);
    }

    #[\Override]
    public static function get_settings_sql($quizid) {
        return [
            'sebversion.enforceversion AS sebversion_enforce',
            'LEFT JOIN {quizaccess_sebversion} sebversion ON sebversion.quizid = quiz.id ',
            [],
        ];
    }
}
