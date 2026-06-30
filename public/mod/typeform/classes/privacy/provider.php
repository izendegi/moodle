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
 * Privacy Subsystem implementation for mod_typeform.
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_typeform\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\userlist_provider;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;

/**
 * Privacy Subsystem implementation for mod_typeform.
 *
 * Provides metadata and methods for managing user data managed by the mod_typeform plugin,
 * ensuring compliance with the privacy API.
 *
 * @package    mod_typeform
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    userlist_provider {
    /**
     * Describe the personal data stored by this plugin.
     */
    public static function get_metadata(collection $collection): collection {
        // Tabla de envíos: contiene datos relacionados con el usuario.
        $collection->add_database_table('typeform_submission', [
                'typeform'      => 'privacy:metadata:typeform_submission:typeform',
                'userid'        => 'privacy:metadata:typeform_submission:userid',
                'submitted'     => 'privacy:metadata:typeform_submission:submitted',
                'timecreated'   => 'privacy:metadata:typeform_submission:timecreated',
                'timemodified'  => 'privacy:metadata:typeform_submission:timemodified',
                'usermodified'  => 'privacy:metadata:typeform_submission:usermodified',
        ], 'privacy:metadata:typeform_submission');

        // La tabla {typeform} suele ser configuración de actividad, normalmente no es "personal data".
        // Si en tu caso el intro o name pueden contener datos personales, podrías documentarlo, pero por defecto no.

        return $collection;
    }

    /**
     * Get contexts containing user data for a user.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module
                  JOIN {typeform} tf ON tf.id = cm.instance
                  JOIN {typeform_submission} tfs ON tfs.typeform = tf.id
                 WHERE ctx.contextlevel = :contextlevel
                   AND m.name = :modname
                   AND tfs.userid = :userid";

        $params = [
                'contextlevel' => CONTEXT_MODULE,
                'modname' => 'typeform',
                'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Export user data in the given approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        $contexts = $contextlist->get_contexts();
        if (empty($contexts)) {
            return;
        }

        [$contextsql, $contextparams] = $DB->get_in_or_equal(
            array_map(function ($ctx) {
                return $ctx->id;
            }, $contexts),
            SQL_PARAMS_NAMED,
            'ctx'
        );

        $sql = "SELECT
                    ctx.id AS contextid,
                    tfs.id,
                    tfs.typeform,
                    tfs.userid,
                    tfs.submitted,
                    tfs.timecreated,
                    tfs.timemodified,
                    tfs.usermodified
                FROM {context} ctx
                JOIN {course_modules} cm ON cm.id = ctx.instanceid
                JOIN {modules} m ON m.id = cm.module
                JOIN {typeform} tf ON tf.id = cm.instance
                JOIN {typeform_submission} tfs ON tfs.typeform = tf.id
               WHERE ctx.contextlevel = :contextlevel
                 AND m.name = :modname
                 AND tfs.userid = :userid
                 AND ctx.id $contextsql
            ORDER BY tfs.timecreated ASC";

        $params = array_merge([
                'contextlevel' => CONTEXT_MODULE,
                'modname' => 'typeform',
                'userid' => $userid,
        ], $contextparams);

        $records = $DB->get_recordset_sql($sql, $params);

        foreach ($records as $rec) {
            $context = context::instance_by_id($rec->contextid);

            $data = (object)[
                    'submitted' => (int)$rec->submitted,
                    'timecreated' => transform::datetime($rec->timecreated),
                    'timemodified' => transform::datetime($rec->timemodified),
                    'usermodified' => $rec->usermodified,
            ];

            // Se escribe bajo una ruta lógica dentro del contexto del módulo.
            writer::with_context($context)->export_data(
                [get_string('pluginname', 'mod_typeform'), 'submissions'],
                $data
            );
        }

        $records->close();
    }

    /**
     * Delete all user data for all users in a context.
     * (e.g., when an activity is deleted)
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('typeform', $context->instanceid, 0, false, MUST_EXIST);
        $instanceid = (int)$cm->instance;

        // Borra todas las filas de submissions asociadas a esta actividad.
        $DB->delete_records('typeform_submission', ['typeform' => $instanceid]);
    }

    /**
     * Delete user data for a user in a set of approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        $contexts = $contextlist->get_contexts();
        if (empty($contexts)) {
            return;
        }

        foreach ($contexts as $context) {
            if (!$context instanceof context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('typeform', $context->instanceid, 0, false, MUST_EXIST);
            $instanceid = (int)$cm->instance;

            $DB->delete_records('typeform_submission', [
                    'typeform' => $instanceid,
                    'userid' => $userid,
            ]);
        }
    }

    /**
     * Get users in context (for bulk operations).
     */
    public static function get_users_in_context(userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('typeform', $context->instanceid, 0, false, MUST_EXIST);
        $instanceid = (int)$cm->instance;

        $sql = "SELECT tfs.userid
                  FROM {typeform_submission} tfs
                 WHERE tfs.typeform = :typeform
                   AND tfs.userid > 0";

        $userlist->add_from_sql('userid', $sql, ['typeform' => $instanceid]);
    }

    /**
     * Delete data for multiple users within a single context.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('typeform', $context->instanceid, 0, false, MUST_EXIST);
        $instanceid = (int)$cm->instance;

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'u');

        $sql = "typeform = :typeform AND userid $insql";
        $params = array_merge(['typeform' => $instanceid], $inparams);

        $DB->delete_records_select('typeform_submission', $sql, $params);
    }
}
