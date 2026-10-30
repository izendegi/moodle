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
 * Completion Progress block.
 *
 * @package    block_completion_progress
 * @copyright  2026 Jonathon Fowler <fowlerj@usq.edu.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_completion_progress;

/**
 * Miscellaneous helpers.
 */
abstract class helpers {
    /**
     * Bumps a value to assist in caching of configured colours in css.php.
     */
    public static function increment_cache_value() {
        $value = get_config('block_completion_progress', 'cachevalue') + 1;
        set_config('cachevalue', $value, 'block_completion_progress');
    }

    /**
     * Checks whether the given page is site-level (Dashboard or Front page) or not.
     *
     * @param moodle_page $tpage the page to check, or the current page if not passed.
     * @return boolean True when on the Dashboard or Site home page.
     */
    public static function on_site_page($tpage = null) {
        global $PAGE;

        $tpage = $tpage ?? $PAGE;
        $context = $tpage->context ?? null;

        if (!$tpage || !$context) {
            return false;
        } else if ($context->contextlevel === CONTEXT_SYSTEM && $tpage->requestorigin === 'restore') {
            return false; // When restoring from a backup, pretend the page is course-level.
        } else if ($context->contextlevel === CONTEXT_COURSE && $context->instanceid == SITEID) {
            return true;  // Front page.
        } else if ($context->contextlevel < CONTEXT_COURSE) {
            return true;  // System, user (i.e. dashboard), course category.
        } else {
            return false;
        }
    }

    /**
     * Checks whether a user holds, in the given context (or its parents), any role
     * whose archetype is 'student'.
     *
     * @param \context $context the context to check (typically the course context).
     * @param int $userid the user id.
     * @return boolean True when the user has at least one student-archetype role.
     */
    public static function user_has_student_role(\context $context, $userid) {
        static $studentroleids = null;

        if ($studentroleids === null) {
            $studentroleids = array_keys(get_archetype_roles('student'));
        }
        if (empty($studentroleids)) {
            return false;
        }

        foreach (get_user_roles($context, $userid, true) as $ra) {
            if (in_array($ra->roleid, $studentroleids)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Resolve a yes/no block setting: instance value, else site default, else code default.
     *
     * @param \stdClass|null $config the block instance configuration.
     * @param string $name the setting name (same for instance and site level).
     * @param int $default the code default.
     * @return boolean
     */
    protected static function resolve_bool_setting($config, $name, $default) {
        if (isset($config->$name) && $config->$name !== '') {
            return (bool)$config->$name;
        }
        $sitedefault = get_config('block_completion_progress', $name);
        if ($sitedefault === false || $sitedefault === '') {
            return (bool)$default;
        }
        return (bool)$sitedefault;
    }

    /**
     * Site-level default for a yes/no block setting, for use as a form default.
     *
     * @param string $name the setting name.
     * @param int $default the code default.
     * @return int
     */
    public static function site_default_bool_setting($name, $default) {
        $sitedefault = get_config('block_completion_progress', $name);
        if ($sitedefault === false || $sitedefault === '') {
            return (int)$default;
        }
        return (int)$sitedefault;
    }

    /**
     * Whether a block instance should show users without a student role their own
     * progress bar on the course page.
     *
     * @param \stdClass|null $config the block instance configuration.
     * @return boolean
     */
    public static function show_bar_to_non_students($config) {
        return self::resolve_bool_setting($config, 'showbarnonstudents', defaults::SHOWBARNONSTUDENTS);
    }

    /**
     * Whether a block instance should show users without a student role, instead of
     * their own bar, the bar of the course's only student (when there is exactly one).
     *
     * @param \stdClass|null $config the block instance configuration.
     * @return boolean
     */
    public static function show_single_student_bar($config) {
        return self::resolve_bool_setting($config, 'showsinglestudentbar', defaults::SHOWSINGLESTUDENTBAR);
    }

    /**
     * Returns the only student of a course, if there is exactly one.
     *
     * A student is a user with an active enrolment and a student-archetype role
     * assigned in the course context.
     *
     * @param \context_course $context the course context.
     * @return \stdClass|null the user record, or null if there are zero or several students.
     */
    public static function get_single_student(\context_course $context) {
        global $DB;
        static $cache = [];

        if (array_key_exists($context->id, $cache)) {
            return $cache[$context->id];
        }
        $cache[$context->id] = null;

        $studentroleids = array_keys(get_archetype_roles('student'));
        if (empty($studentroleids)) {
            return null;
        }

        $enroljoin = get_enrolled_with_capabilities_join($context, '', '', 0, true);
        [$rolesql, $roleparams] = $DB->get_in_or_equal($studentroleids, SQL_PARAMS_NAMED, 'ssrole');
        $sql = "SELECT DISTINCT u.id
                  FROM {user} u
                       {$enroljoin->joins}
                  JOIN {role_assignments} ssra ON ssra.userid = u.id
                       AND ssra.contextid = :sscontextid
                       AND ssra.roleid {$rolesql}
                 WHERE {$enroljoin->wheres}";
        $params = array_merge($enroljoin->params, $roleparams, ['sscontextid' => $context->id]);

        // Two records are enough to know there is more than one student.
        $records = $DB->get_records_sql($sql, $params, 0, 2);
        if (count($records) === 1) {
            $cache[$context->id] = \core_user::get_user(array_key_first($records)) ?: null;
        }
        return $cache[$context->id];
    }
}
