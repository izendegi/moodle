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
 * Database enrolment plugin.
 *
 * This plugin synchronises enrolment and roles with external database table.
 *
 * @package    enrol_database
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @copyright  2013 Iñaki Arenaza {@link https://www.mondragon.edu}
 * @copyright  2016 Julen Pardo {@link https://www.mondragon.edu}
 * @copyright  2019 Kepa Urzelai {@link https://www.mondragon.edu}
 * @copyright  2023 Iñigo Zendegi {@link https://www.mondragon.edu}
 * @copyright  2024 Ibai Mutiloa {@link https://www.mondragon.edu}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Database enrolment plugin implementation.
 * @author  Petr Skoda - based on code by Martin Dougiamas, Martin Langhoff and others
 * @author  Iñaki Arenaza
 * @author  Julen Pardo
 * @author  Kepa Urzelai
 * @author  Iñigo Zendegi
 * @author  Ibai Mutiloa
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_database_plugin extends enrol_plugin {
    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        if (!has_capability('enrol/database:config', $context)) {
            return false;
        }
        if (!enrol_is_enabled('database')) {
            return true;
        }
        if (!$this->get_config('dbtype') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            return true;
        }

        //TODO: connect to external system and make sure no users are to be enrolled in this course
        return false;
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/database:config', $context);
    }

    /**
     * Does this plugin allow manual unenrolment of a specific user?
     * Yes, but only if user suspended...
     *
     * @param stdClass $instance course enrol instance
     * @param stdClass $ue record from user_enrolments table
     *
     * @return bool - true means user with 'enrol/xxx:unenrol' may unenrol this user, false means nobody may touch this user enrolment
     */
    public function allow_unenrol_user(stdClass $instance, stdClass $ue) {
        if ($ue->status == ENROL_USER_SUSPENDED) {
            return true;
        }

        return false;
    }

    /**
     * Forces synchronisation of user enrolments with external database,
     * does not create new courses.
     *
     * @param stdClass $user user record
     * @return void
     */
    public function sync_user_enrolments($user) {
        global $CFG, $DB;

        // We do not create courses here intentionally because it requires full sync and is slow.
        if (!$this->get_config('dbtype') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            return;
        }

        $table            = trim($this->get_config('remoteenroltable'));
        $coursefield      = trim($this->get_config('remotecoursefield'));
        $userfield        = trim($this->get_config('remoteuserfield'));
        $rolefield        = trim($this->get_config('remoterolefield'));
        $otheruserfield   = trim($this->get_config('remoteotheruserfield'));

        // Lowercased versions - necessary because we normalise the resultset with array_change_key_case().
        $coursefield_l    = strtolower($coursefield);
        $userfield_l      = strtolower($userfield);
        $rolefield_l      = strtolower($rolefield);
        $otheruserfieldlower = strtolower($otheruserfield);

        $localrolefield   = $this->get_config('localrolefield');
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');

        $unenrolaction    = $this->get_config('unenrolaction');
        $defaultrole      = $this->get_config('defaultrole');

        $manualenrolcleaning = $this->get_config('manualenrol_cleaning');
        $manualenrolcleaningmode = $this->get_config('manualenrol_cleaning_mode');

        $ignorehidden     = $this->get_config('ignorehiddencourses');

        if (!is_object($user) or !property_exists($user, 'id')) {
            throw new coding_exception('Invalid $user parameter in sync_user_enrolments()');
        }

        if (!property_exists($user, $localuserfield)) {
            debugging('Invalid $user parameter in sync_user_enrolments(), missing '.$localuserfield);
            $user = $DB->get_record('user', array('id'=>$user->id));
        }

        // Create roles mapping.
        $allroles = get_all_roles();
        if (!isset($allroles[$defaultrole])) {
            $defaultrole = 0;
        }
        $roles = array();
        foreach ($allroles as $role) {
            $roles[$role->$localrolefield] = $role->id;
        }

        $roleassigns = array();
        $enrols = array();
        $instances = array();

        if (!$extdb = $this->db_init()) {
            // Can not connect to database, sorry.
            return;
        }

        // Read remote enrols and create instances.
        [$sql, $extparams] = $this->db_get_query($extdb, $table, [$userfield => $user->$localuserfield], [], false);

        if ($rs = $extdb->Execute($sql, $extparams)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);

                    if (empty($fields[$coursefield_l])) {
                        // Missing course info.
                        continue;
                    }
                    if (!$course = $DB->get_record('course', array($localcoursefield=>$fields[$coursefield_l]), 'id,visible')) {
                        continue;
                    }
                    if (!$course->visible and $ignorehidden) {
                        continue;
                    }

                    if (empty($fields[$rolefield_l]) or !isset($roles[$fields[$rolefield_l]])) {
                        if (!$defaultrole) {
                            // Role is mandatory.
                            continue;
                        }
                        $roleid = $defaultrole;
                    } else {
                        $roleid = $roles[$fields[$rolefield_l]];
                    }

                    $roleassigns[$course->id][$roleid] = $roleid;
                    if (empty($fields[$otheruserfieldlower])) {
                        $enrols[$course->id][$roleid] = $roleid;
                    }

                    if ($instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'database'), '*', IGNORE_MULTIPLE)) {
                        $instances[$course->id] = $instance;
                        continue;
                    }

                    $timeout = 5;
                    $locktype = 'enrol_database_user_enrolments';
                    $resource = 'course:' . $course->id;
                    $lockfactory = \core\lock\lock_config::get_lock_factory($locktype);
                    if ($lock = $lockfactory->get_lock($resource, $timeout)) {
                        try {
                            $instance = $DB->get_record('enrol', ['enrol' => 'database', 'courseid' => $course->id]);
                            if (!$instance) {
                                $enrolid = $this->add_instance($course);
                                $instance = $DB->get_record('enrol', ['id' => $enrolid]);
                            }
                        } finally {
                            $lock->release();
                        }
                    } else {
                        // Attempt to reuse an existing record added by another process during race condition.
                        if ($instance = $DB->get_record('enrol', ['enrol' => 'database', 'courseid' => $course->id])) {
                            $instances[$course->id] = $instance;
                            continue;
                        } else {
                            // Give up.
                            throw new moodle_exception(
                                'locktimeout',
                                'enrol_database',
                                '',
                                null,
                                'Could not create database enrolment instance for course ' . $course->id
                            );
                        }
                    }
                    $instances[$course->id] = $instance;
                }
            }
            $rs->Close();
            // The connection is kept open for the group memberships synchronisation.
        } else {
            // Bad luck, something is wrong with the db connection.
            $extdb->Close();
            return;
        }

        // Enrol user into courses and sync roles.
        foreach ($roleassigns as $courseid => $roles) {
            if (!isset($instances[$courseid])) {
                // Ignored.
                continue;
            }
            $instance = $instances[$courseid];

            $newenrolment = false;
            if (isset($enrols[$courseid])) {
                if ($e = $DB->get_record('user_enrolments', array('userid' => $user->id, 'enrolid' => $instance->id))) {
                    // Reenable enrolment when previously disable enrolment refreshed.
                    if ($e->status == ENROL_USER_SUSPENDED) {
                        $this->update_user_enrol($instance, $user->id, ENROL_USER_ACTIVE);
                    }
                } else {
                    $roleid = reset($enrols[$courseid]);
                    $this->enrol_user($instance, $user->id, $roleid, 0, 0, ENROL_USER_ACTIVE);
                    $newenrolment = true;
                }
            }

            if (!$context = context_course::instance($instance->courseid, IGNORE_MISSING)) {
                // Weird.
                continue;
            }
            $current = $DB->get_records('role_assignments', array('contextid'=>$context->id, 'userid'=>$user->id, 'component'=>'enrol_database', 'itemid'=>$instance->id), '', 'id, roleid');

            $existing = array();
            foreach ($current as $r) {
                if (isset($roles[$r->roleid])) {
                    $existing[$r->roleid] = $r->roleid;
                } else {
                    role_unassign($r->roleid, $user->id, $context->id, 'enrol_database', $instance->id);
                }
            }
            foreach ($roles as $rid) {
                if (!isset($existing[$rid])) {
                    role_assign($rid, $user->id, $context->id, 'enrol_database', $instance->id);
                }
            }

            // Remove the duplicated manual enrolment, as the scheduled synchronisation does: in "new" mode only when the
            // user has just been enrolled, in "full" mode whenever the user is enrolled from the external database.
            if ($manualenrolcleaning and isset($enrols[$courseid])
                    and ($newenrolment or $manualenrolcleaningmode == 'full')) {
                $this->remove_user_duplicated_manual_enrolment($user, $courseid, $enrols[$courseid]);
            }
        }

        // Unenrol as necessary.
        $sql = "SELECT e.*, c.visible AS cvisible, ue.status AS ustatus
                  FROM {enrol} e
                  JOIN {course} c ON c.id = e.courseid
                  JOIN {role_assignments} ra ON ra.itemid = e.id
             LEFT JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = ra.userid
                 WHERE ra.userid = :userid AND e.enrol = 'database'";
        $rs = $DB->get_recordset_sql($sql, array('userid' => $user->id));
        foreach ($rs as $instance) {
            if (!$instance->cvisible and $ignorehidden) {
                continue;
            }

            if (!$context = context_course::instance($instance->courseid, IGNORE_MISSING)) {
                // Very weird.
                continue;
            }

            if (!empty($enrols[$instance->courseid])) {
                // We want this user enrolled.
                continue;
            }

            // Deal with enrolments removed from external table
            if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                $this->unenrol_user($instance, $user->id);

            } else if ($unenrolaction == ENROL_EXT_REMOVED_KEEP) {
                // Keep - only adding enrolments.

            } else if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND or $unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                // Suspend users.
                if ($instance->ustatus != ENROL_USER_SUSPENDED) {
                    $this->update_user_enrol($instance, $user->id, ENROL_USER_SUSPENDED);
                }
                if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                    if (!empty($roleassigns[$instance->courseid])) {
                        // We want this "other user" to keep their roles.
                        continue;
                    }
                    role_unassign_all(array('contextid'=>$context->id, 'userid'=>$user->id, 'component'=>'enrol_database', 'itemid'=>$instance->id));
                }
            }
        }
        $rs->close();

        // Group memberships are synchronised once the user is enrolled in the courses, reusing the connection.
        $this->sync_user_group_enrolments($user, $extdb);
        $extdb->Close();
    }

    /**
     * Removes the duplicated manual enrolment of a user that logs in, for the roles of the external database.
     *
     * It runs on login, so errors are not thrown: they are written to the server error log and reported with
     * debugging(), and the manual enrolment is removed later by the scheduled task if its mode is "full".
     *
     * @param stdClass $user user record.
     * @param int $courseid
     * @param int[] $roleids roles assigned by the external database in the course.
     * @return void
     */
    protected function remove_user_duplicated_manual_enrolment(stdClass $user, int $courseid, array $roleids): void {
        $localuserfield = $this->get_config('localuserfield');
        // The course mapping is only used in the trace, which is not shown on login.
        $course = (object) ['id' => $courseid, 'mapping' => $courseid];
        $trace = new null_progress_trace();
        try {
            foreach ($roleids as $roleid) {
                if ($this->remove_duplicated_manual_enrolment($course, $user->id, $roleid,
                        (string) ($user->$localuserfield ?? ''), $trace)) {
                    // The user is no longer manually enrolled, no need to check the other roles.
                    break;
                }
            }
        } catch (Exception $e) {
            // A failure must not prevent the user from logging in.
            $message = 'enrol_database: error removing the duplicated manual enrolment of user ' . $user->id .
                ' in course ' . $courseid . ': ' . $e->getMessage();
            error_log($message);
            debugging($message, DEBUG_DEVELOPER, $e->getTrace());
        }
    }

    /**
     * Forces synchronisation of user group enrolments with external database,
     * does not create new groups.
     *
     * It runs on login, so errors are not thrown: they are written to the server error log and reported with
     * debugging(), and the memberships are synchronised later by the scheduled task.
     *
     * @param stdClass $user user record
     * @param ADOConnection|null $extdb open connection to the external database, a new one is opened if not given.
     * @return void
     */
    public function sync_user_group_enrolments($user, ?ADOConnection $extdb = null) {
        if (!$this->is_group_enrolment_sync_configured()) {
            return;
        }

        $ownconnection = !$extdb;
        if ($ownconnection and !$extdb = $this->db_init()) {
            return;
        }

        try {
            $this->sync_group_memberships($extdb, new null_progress_trace(), $user);
        } catch (Exception $e) {
            // A failure must not prevent the user from logging in. It is always logged in the server error log, as
            // debugging() only shows it on sites in developer mode.
            $message = 'enrol_database: error synchronising the group memberships of user ' . $user->id .
                ' with the external database: ' . $e->getMessage();
            error_log($message);
            debugging($message, DEBUG_DEVELOPER, $e->getTrace());
        } finally {
            if ($ownconnection) {
                $extdb->Close();
            }
        }
    }

    /**
     * Forces synchronisation of all enrolments with external database.
     *
     * @param progress_trace $trace
     * @param null|int $onecourse limit sync to one course only (used primarily in restore)
     * @return int 0 means success, 1 db connect failure, 2 db read failure
     */
    public function sync_enrolments(progress_trace $trace, $onecourse = null) {
        global $CFG, $DB;

        // We do not create courses here intentionally because it requires full sync and is slow.
        if (!$this->get_config('dbtype') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            $trace->output('User enrolment synchronisation skipped.');
            $trace->finished();
            return 0;
        }

        $trace->output("\nStarting user enrolment synchronisation...");

        if ($this->is_user_creation_misconfigured()) {
            $trace->output("warning: users can not be created from the remote users table, because no field of it is " .
                "mapped to the local user field " . $this->get_config('localuserfield'), 1);
        }

        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return 1;
        }

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $table            = trim($this->get_config('remoteenroltable'));
        $coursefield      = trim($this->get_config('remotecoursefield'));
        $userfield        = trim($this->get_config('remoteuserfield'));
        $rolefield        = trim($this->get_config('remoterolefield'));
        $otheruserfield   = trim($this->get_config('remoteotheruserfield'));

        // Lowercased versions - necessary because we normalise the resultset with array_change_key_case().
        $coursefield_l    = strtolower($coursefield);
        $userfield_l      = strtolower($userfield);
        $rolefield_l      = strtolower($rolefield);
        $otheruserfieldlower = strtolower($otheruserfield);

        $localrolefield   = $this->get_config('localrolefield');
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');

        $unenrolaction    = $this->get_config('unenrolaction');
        $defaultrole      = $this->get_config('defaultrole');

        $manualenrolcleaning = $this->get_config('manualenrol_cleaning');
        $manualenrolcleaningmode = $this->get_config('manualenrol_cleaning_mode');

        // Create roles mapping.
        $allroles = get_all_roles();
        if (!isset($allroles[$defaultrole])) {
            $defaultrole = 0;
        }
        $roles = array();
        foreach ($allroles as $role) {
            $roles[$role->$localrolefield] = $role->id;
        }

        if ($onecourse) {
            $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, c.shortname, e.id AS enrolid
                      FROM {course} c
                 LEFT JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'database')
                     WHERE c.id = :id";
            if (!$course = $DB->get_record_sql($sql, array('id'=>$onecourse))) {
                // Course does not exist, nothing to sync.
                return 0;
            }
            if (empty($course->mapping)) {
                // We can not map to this course, sorry.
                return 0;
            }
            if (empty($course->enrolid)) {
                $course->enrolid = $this->add_instance($course);
            }
            $existing = array($course->mapping=>$course);

            // Feel free to unenrol everybody, no safety tricks here.
            $preventfullunenrol = false;
            // Course being restored are always hidden, we have to ignore the setting here.
            $ignorehidden = false;

        } else {
            // Get a list of courses to be synced that are in external table.
            $externalcourses = array();
            [$sql, $extparams] = $this->db_get_query($extdb, $table, [], [$coursefield], true);
            if ($rs = $extdb->Execute($sql, $extparams)) {
                if (!$rs->EOF) {
                    while ($mapping = $rs->FetchRow()) {
                        $mapping = reset($mapping);
                        $mapping = $this->db_decode($mapping);
                        if (empty($mapping)) {
                            // invalid mapping
                            continue;
                        }
                        $externalcourses[$mapping] = true;
                    }
                }
                $rs->Close();
            } else {
                $trace->output('Error reading data from the external enrolment table');
                $extdb->Close();
                return 2;
            }
            $preventfullunenrol = empty($externalcourses);
            if ($preventfullunenrol and $unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                $trace->output('Preventing unenrolment of all current users, because it might result in major data loss, there has to be at least one record in external enrol table, sorry.', 1);
            }

            // First find all existing courses with enrol instance.
            $existing = array();
            $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, e.id AS enrolid, c.shortname
                      FROM {course} c
                      JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'database')";
            $rs = $DB->get_recordset_sql($sql); // Watch out for idnumber duplicates.
            foreach ($rs as $course) {
                if (empty($course->mapping)) {
                    continue;
                }
                $existing[$course->mapping] = $course;
                unset($externalcourses[$course->mapping]);
            }
            $rs->close();

            // Add necessary enrol instances that are not present yet.
            $params = array();
            $localnotempty = "";
            if ($localcoursefield !== 'id') {
                $localnotempty =  "AND c.$localcoursefield <> :lcfe";
                $params['lcfe'] = '';
            }
            $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, c.shortname
                      FROM {course} c
                 LEFT JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'database')
                     WHERE e.id IS NULL $localnotempty";
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $course) {
                if (empty($course->mapping)) {
                    continue;
                }
                if (!isset($externalcourses[$course->mapping])) {
                    // Course not synced or duplicate.
                    continue;
                }
                $course->enrolid = $this->add_instance($course);
                $existing[$course->mapping] = $course;
                unset($externalcourses[$course->mapping]);
            }
            $rs->close();

            // Print list of missing courses.
            if ($externalcourses) {
                $list = implode(', ', array_keys($externalcourses));
                $trace->output("error: following courses do not exist - $list", 1);
                unset($list);
            }

            // Free memory.
            unset($externalcourses);

            $ignorehidden = $this->get_config('ignorehiddencourses');
        }

        // Users not found in Moodle that could not be created from the remote users table, so that they are only
        // searched there once.
        $notcreatedusers = [];

        // Sync user enrolments.
        $sqlfields = array($userfield);
        if ($rolefield) {
            $sqlfields[] = $rolefield;
        }
        if ($otheruserfield) {
            $sqlfields[] = $otheruserfield;
        }
        foreach ($existing as $course) {
            if ($ignorehidden and !$course->visible) {
                continue;
            }
            if (!$instance = $DB->get_record('enrol', array('id'=>$course->enrolid))) {
                continue; // Weird!
            }
            $context = context_course::instance($course->id);
            // Participants page of the course, for the trace.
            $courseurl = $CFG->wwwroot . '/user/index.php?id=' . $course->id;

            // Get current list of enrolled users with their roles.
            $currentroles  = array();
            $currentenrols = array();
            $currentstatus = array();
            $usermapping   = array();
            // Identifier of each user in the external database, for the trace.
            $useridentifier = [];
            $sql = "SELECT u.$localuserfield AS mapping, u.id AS userid, ue.status, ra.roleid
                      FROM {user} u
                      JOIN {role_assignments} ra ON (ra.userid = u.id AND ra.component = 'enrol_database' AND ra.itemid = :enrolid)
                 LEFT JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = ra.itemid)
                     WHERE u.deleted = 0";
            $params = array('enrolid'=>$instance->id);
            if ($localuserfield === 'username') {
                $sql .= " AND u.mnethostid = :mnethostid";
                $params['mnethostid'] = $CFG->mnet_localhost_id;
            }
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                $currentroles[$ue->userid][$ue->roleid] = $ue->roleid;
                $usermapping[$ue->mapping] = $ue->userid;
                $useridentifier[$ue->userid] = $ue->mapping;

                if (isset($ue->status)) {
                    $currentenrols[$ue->userid][$ue->roleid] = $ue->roleid;
                    $currentstatus[$ue->userid] = $ue->status;
                }
            }
            $rs->close();

            // Get list of users that need to be enrolled and their roles.
            $requestedroles  = array();
            $requestedenrols = array();
            [$sql, $extparams] = $this->db_get_query($extdb, $table, [$coursefield => $course->mapping], $sqlfields);
            if ($rs = $extdb->Execute($sql, $extparams)) {
                // Read all the rows before processing them, because unknown users may be searched in the remote users
                // table, and some drivers can not run a query while another result set is open.
                $extrows = $rs->EOF ? [] : $rs->GetArray();
                $rs->Close();
                if ($extrows) {
                    $usersearch = array('deleted' => 0);
                    if ($localuserfield === 'username') {
                        $usersearch['mnethostid'] = $CFG->mnet_localhost_id;
                    }
                    foreach ($extrows as $fields) {
                        $fields = array_change_key_case($fields, CASE_LOWER);
                        if (empty($fields[$userfield_l])) {
                            $trace->output("error: skipping user without mandatory $localuserfield in course " .
                                "$course->mapping: " . $courseurl, 1);
                            continue;
                        }
                        $mapping = $fields[$userfield_l];
                        if (!isset($usermapping[$mapping])) {
                            $usersearch[$localuserfield] = $mapping;
                            if ($user = $DB->get_record('user', $usersearch, 'id', IGNORE_MULTIPLE)) {
                                $userid = $user->id;
                            } else if (!isset($notcreatedusers[$mapping])) {
                                // Try to create the unknown user from the remote users table.
                                $userid = $this->create_user_from_remote($extdb, $mapping, $trace);
                            } else {
                                $userid = null;
                            }
                            if (!$userid) {
                                $notcreatedusers[$mapping] = true;
                                $trace->output("error: skipping unknown $localuserfield '$mapping' in course " .
                                    "$course->mapping: " . $courseurl, 1);
                                continue;
                            }
                            $usermapping[$mapping] = $userid;
                        } else {
                            $userid = $usermapping[$mapping];
                        }
                        if (empty($fields[$rolefield_l]) or !isset($roles[$fields[$rolefield_l]])) {
                            if (!$defaultrole) {
                                $trace->output("error: skipping user $localuserfield '$mapping' in course " .
                                    "$course->mapping - missing course and default role: " . $courseurl, 1);
                                continue;
                            }
                            $roleid = $defaultrole;
                        } else {
                            $roleid = $roles[$fields[$rolefield_l]];
                        }

                        $requestedroles[$userid][$roleid] = $roleid;
                        if (empty($fields[$otheruserfieldlower])) {
                            $requestedenrols[$userid][$roleid] = $roleid;
                        }
                        $useridentifier[$userid] = $mapping;
                    }
                }
                unset($extrows);
            } else {
                $trace->output("error: skipping course $course->mapping - could not match with external database: " .
                    $courseurl, 1);
                continue;
            }
            unset($usermapping);

            // In full mode, find with a single query which users are manually enrolled in the course, so that the
            // duplicated manual enrolment check below only runs for them.
            $manualusers = [];
            if ($manualenrolcleaning and $manualenrolcleaningmode == 'full') {
                $manualusers = $DB->get_records_sql_menu("SELECT DISTINCT ue.userid, 1
                                                            FROM {user_enrolments} ue
                                                            JOIN {enrol} e ON e.id = ue.enrolid
                                                           WHERE e.enrol = 'manual'
                                                             AND e.courseid = :courseid", ['courseid' => $course->id]);
            }

            // Enrol all users and sync roles.
            foreach ($requestedenrols as $userid => $userroles) {
                foreach ($userroles as $roleid) {
                    if (empty($currentenrols[$userid])) {
                        $this->enrol_user($instance, $userid, $roleid, 0, 0, ENROL_USER_ACTIVE);
                        $currentroles[$userid][$roleid] = $roleid;
                        $currentenrols[$userid][$roleid] = $roleid;
                        $currentstatus[$userid] = ENROL_USER_ACTIVE;
                        $trace->output("enrolling $localuserfield '" . $useridentifier[$userid] . "' as " .
                            $allroles[$roleid]->shortname . " ==> $course->mapping: " . $courseurl, 1);

                        // If new mode is enabled, remove the duplicated manual enrolment of the recently enrolled user.
                        if ($manualenrolcleaning and $manualenrolcleaningmode == 'new') {
                            $this->remove_duplicated_manual_enrolment($course, $userid, $roleid, $useridentifier[$userid],
                                $trace);
                        }
                    }
                    // If full mode is enabled, remove the duplicated manual enrolment of every manually enrolled user in the
                    // external database.
                    if ($manualenrolcleaning and $manualenrolcleaningmode == 'full' and isset($manualusers[$userid])) {
                        if ($this->remove_duplicated_manual_enrolment($course, $userid, $roleid, $useridentifier[$userid],
                                $trace)) {
                            // The user is no longer manually enrolled, no need to check the other roles.
                            unset($manualusers[$userid]);
                        }
                    }
                }

                // Reenable enrolment when previously disable enrolment refreshed.
                if ($currentstatus[$userid] == ENROL_USER_SUSPENDED) {
                    $this->update_user_enrol($instance, $userid, ENROL_USER_ACTIVE);
                    $trace->output("unsuspending $localuserfield '" . $useridentifier[$userid] . "' ==> $course->mapping: " .
                        $courseurl, 1);
                }
            }

            foreach ($requestedroles as $userid => $userroles) {
                // Assign extra roles.
                foreach ($userroles as $roleid) {
                    if (empty($currentroles[$userid][$roleid])) {
                        role_assign($roleid, $userid, $context->id, 'enrol_database', $instance->id);
                        $currentroles[$userid][$roleid] = $roleid;
                        $trace->output("assigning user roles: $localuserfield '" . $useridentifier[$userid] . "' as " .
                            $allroles[$roleid]->shortname . " ==> $course->mapping: " . $courseurl, 1);
                    }
                }

                // Unassign removed roles.
                foreach ($currentroles[$userid] as $cr) {
                    if (empty($userroles[$cr])) {
                        role_unassign($cr, $userid, $context->id, 'enrol_database', $instance->id);
                        unset($currentroles[$userid][$cr]);
                        $trace->output("unassigning user roles: " . $allroles[$cr]->shortname . " role to $localuserfield '" .
                            $useridentifier[$userid] . "' ==> $course->mapping: " . $courseurl, 1);
                    }
                }

                unset($currentroles[$userid]);
            }

            foreach ($currentroles as $userid => $userroles) {
                // These are roles that exist only in Moodle, not the external database
                // so make sure the unenrol actions will handle them by setting status.
                $currentstatus += array($userid => ENROL_USER_ACTIVE);
            }

            // Deal with enrolments removed from external table.
            if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                if (!$preventfullunenrol) {
                    // Unenrol.
                    foreach ($currentstatus as $userid => $status) {
                        if (isset($requestedenrols[$userid])) {
                            continue;
                        }
                        $this->unenrol_user($instance, $userid);
                        $trace->output("unenrolling user: $localuserfield '" . $useridentifier[$userid] .
                            "' ==> $course->mapping: " . $courseurl, 1);
                    }
                }

            } else if ($unenrolaction == ENROL_EXT_REMOVED_KEEP) {
                // Keep - only adding enrolments.

            } else if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND or $unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                // Suspend enrolments.
                foreach ($currentstatus as $userid => $status) {
                    if (isset($requestedenrols[$userid])) {
                        continue;
                    }
                    if ($status != ENROL_USER_SUSPENDED) {
                        $this->update_user_enrol($instance, $userid, ENROL_USER_SUSPENDED);
                        $trace->output("suspending user: $localuserfield '" . $useridentifier[$userid] .
                            "' ==> $course->mapping: " . $courseurl, 1);
                    }
                    if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                        if (isset($requestedroles[$userid])) {
                            // We want this "other user" to keep their roles.
                            continue;
                        }
                        role_unassign_all(array('contextid'=>$context->id, 'userid'=>$userid, 'component'=>'enrol_database', 'itemid'=>$instance->id));

                        $trace->output("unassigning user all roles: $localuserfield '" . $useridentifier[$userid] .
                            "' ==> $course->mapping: " . $courseurl, 1);
                    }
                }
            }
        }

        // Close db connection.
        $extdb->Close();

        $trace->output("...user enrolment synchronisation finished.");
        $trace->finished();

        return 0;
    }

    /**
     * Whether the remote users table is set but users can not be created from it.
     *
     * Users are searched in the remote users table by the field mapped to the local user field, so they can not be
     * created when it has no field mapped (always when the local user field is id).
     *
     * @return bool true if the remote users table is set and no field of it is mapped to the local user field.
     */
    protected function is_user_creation_misconfigured(): bool {
        if (trim((string) $this->get_config('newusertable')) === '') {
            return false;
        }
        return trim((string) $this->get_config('newuser' . $this->get_config('localuserfield'))) === '';
    }

    /**
     * Creates a user that does not exist in Moodle with the data found in the remote users table.
     *
     * The user is searched in the remote users table by the column mapped to the local user field, so users can not be
     * created when the local user field is id. The authentication method is the default one, unless the remote auth
     * column has a value. The password is not set, so the authentication method must not depend on it.
     *
     * @param ADOConnection $extdb connection to the external database.
     * @param string $mapping value of the local user field of the user in the remote enrolments table.
     * @param progress_trace $trace
     * @return int|null id of the new user, null if it has not been created.
     */
    protected function create_user_from_remote(ADOConnection $extdb, string $mapping, progress_trace $trace): ?int {
        global $CFG, $DB;

        $table = trim((string) $this->get_config('newusertable'));
        $localuserfield = $this->get_config('localuserfield');

        // Remote columns of each user field, the ones without a column are not filled.
        $columns = [];
        foreach (['username', 'email', 'firstname', 'lastname', 'idnumber'] as $field) {
            $column = trim((string) $this->get_config('newuser' . $field));
            if ($column !== '') {
                $columns[$field] = $column;
            }
        }
        $authcolumn = trim((string) $this->get_config('newuserauthfield'));

        if ($table === '' || empty($columns[$localuserfield])) {
            // User creation disabled, or the user can not be searched in the remote users table.
            return null;
        }

        $sqlfields = array_values($columns);
        if ($authcolumn !== '') {
            $sqlfields[] = $authcolumn;
        }
        // The mapping comes from the remote table as is, and db_get_query() encodes it again.
        $conditions = [$columns[$localuserfield] => $this->db_decode($mapping)];
        [$sql, $extparams] = $this->db_get_query($extdb, $table, $conditions, $sqlfields);
        if (!$rs = $extdb->Execute($sql, $extparams)) {
            $trace->output("error: could not read the remote users table to create $localuserfield '$mapping'", 1);
            return null;
        }
        $fields = $rs->EOF ? false : $rs->FetchRow();
        $rs->Close();
        if (!$fields) {
            // The user is not in the remote users table either.
            return null;
        }
        $fields = array_change_key_case($fields, CASE_LOWER);

        $user = new stdClass();
        foreach ($columns as $field => $column) {
            $user->$field = trim((string) $this->db_decode($fields[strtolower($column)] ?? ''));
        }
        // Moodle usernames are lowercase.
        $user->username = core_text::strtolower($user->username);
        // Keep the exact value of the remote enrolments table, so the user is found in the next synchronisations.
        $user->$localuserfield = $mapping;
        $user->auth = $this->get_config('newuserauth') ?: 'manual';
        if ($authcolumn !== '' && !empty($fields[strtolower($authcolumn)])) {
            $user->auth = trim($this->db_decode($fields[strtolower($authcolumn)]));
        }
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->confirmed = 1;

        // Check the user data before creating it.
        $error = null;
        $emailselect = $DB->sql_equal('email', ':email', false) . ' AND deleted = 0';
        $missing = array_filter(['username', 'email', 'firstname', 'lastname'], fn($field) => ($user->$field ?? '') === '');
        if ($missing) {
            $error = 'missing mandatory ' . implode(', ', $missing);
        } else if (!is_enabled_auth($user->auth) || !exists_auth_plugin($user->auth)) {
            $error = "authentication method '$user->auth' not enabled";
        } else if (!validate_email($user->email)) {
            $error = "invalid email '$user->email'";
        } else if ($DB->record_exists('user', ['username' => $user->username, 'mnethostid' => $user->mnethostid])) {
            $error = "username '$user->username' already used by another user";
        } else if (empty($CFG->allowaccountssameemail) &&
                $DB->record_exists_select('user', $emailselect, ['email' => $user->email])) {
            $error = "email '$user->email' already used by another user";
        } else if (!empty($user->idnumber) &&
                $DB->record_exists('user', ['idnumber' => $user->idnumber, 'deleted' => 0])) {
            $error = "idnumber '$user->idnumber' already used by another user";
        }
        if (!$error) {
            require_once($CFG->dirroot . '/user/lib.php');
            try {
                $userid = user_create_user($user, false);
            } catch (moodle_exception $e) {
                $error = $e->getMessage();
            }
        }
        if ($error) {
            $trace->output("error: could not create $localuserfield '$mapping' from the remote users table - $error", 1);
            return null;
        }

        $trace->output("creating user $localuserfield '$mapping' from the remote users table: " .
            $CFG->wwwroot . '/user/view.php?id=' . $userid, 1);
        return $userid;
    }

    /**
     * Performs a full sync with external database.
     *
     * It creates new courses if necessary,
     * to then enrol and unenrol users.
     *
     * @param progress_trace $trace
     * @return int 0 means success, 1 db connect failure, 4 db read failure
     */
    public function sync_courses(progress_trace $trace) {
        global $CFG, $DB;

        // Make sure we sync either enrolments or courses.
        if (!$this->get_config('dbtype') or !$this->get_config('newcoursetable') or !$this->get_config('newcoursefullname') or !$this->get_config('newcourseshortname')) {
            $trace->output('Course synchronisation skipped.');
            $trace->finished();
            return 0;
        }

        $trace->output("\nStarting course synchronisation...");

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return 1;
        }

        $courseconfig = get_config('moodlecourse');

        $table        = trim($this->get_config('newcoursetable'));
        $fullname     = trim($this->get_config('newcoursefullname'));
        $shortname    = trim($this->get_config('newcourseshortname'));
        $idnumber     = trim($this->get_config('newcourseidnumber'));
        // The optional settings added by this plugin may not be saved yet, so they are cast to string.
        $summary      = trim((string) $this->get_config('newcoursesummary'));
        $template     = trim((string) $this->get_config('newcoursetemplate'));
        $category     = trim($this->get_config('newcoursecategory'));
        $categoryname = trim((string) $this->get_config('newcoursecategorypath'));

        $startdate    = trim($this->get_config('newcoursestartdate'));
        $enddate      = trim($this->get_config('newcourseenddate'));

        // Lowercased versions - necessary because we normalise the resultset with array_change_key_case().
        $fullname_l  = strtolower($fullname);
        $shortname_l = strtolower($shortname);
        $idnumber_l  = strtolower($idnumber);
        $summary_l   = strtolower($summary);
        $template_l  = strtolower($template);
        $category_l  = strtolower($category);
        $categoryname_l = strtolower($categoryname);
        $startdatelowercased = strtolower($startdate);
        $enddatelowercased   = strtolower($enddate);

        $localcategoryfield = $this->get_config('localcategoryfield', 'id');
        $defaultcategory    = $this->get_config('defaultcategory');

        if (null === ($defaultcategory = core_course_category::get($defaultcategory, IGNORE_MISSING, true))) {
            $trace->output("default course category does not exist!", 1);
            $defaultcategory = core_course_category::get_default();
        }

        $sqlfields = array($fullname, $shortname);
        if ($category) {
            $sqlfields[] = $category;
        }
        if ($summary) {
            $sqlfields[] = $summary;
        }
        if ($template) {
            $sqlfields[] = $template;
        }
        if ($idnumber) {
            $sqlfields[] = $idnumber;
        }
        if ($categoryname) {
            $sqlfields[] = $categoryname;
        }
        if ($startdate) {
            $sqlfields[] = $startdate;
        }
        if ($enddate) {
            $sqlfields[] = $enddate;
        }

        [$sql, $extparams] = $this->db_get_query($extdb, $table, [], $sqlfields, true);
        $createcourses = array();
        if ($rs = $extdb->Execute($sql, $extparams)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);
                    if (empty($fields[$shortname_l]) or empty($fields[$fullname_l])) {
                        $trace->output('error: invalid external course record, shortname and fullname are mandatory: ' . json_encode($fields), 1); // Hopefully every geek can read JS, right?
                        continue;
                    }
                    if ($DB->record_exists('course', array('shortname'=>$fields[$shortname_l]))) {
                        // Already exists, skip.
                        continue;
                    }
                    // Allow empty idnumber but not duplicates.
                    if ($idnumber and $fields[$idnumber_l] !== '' and $fields[$idnumber_l] !== null and $DB->record_exists('course', array('idnumber'=>$fields[$idnumber_l]))) {
                        $trace->output('error: duplicate idnumber, can not create course: '.$fields[$shortname_l].' ['.$fields[$idnumber_l].']', 1);
                        continue;
                    }
                    $course = new stdClass();
                    $course->fullname  = $fields[$fullname_l];
                    $course->shortname = $fields[$shortname_l];
                    $course->idnumber  = $idnumber_l ? $fields[$idnumber_l] : '';
                    $course->summary   = $summary_l ? $fields[$summary_l] : '';
                    $course->template  = $template_l ? trim((string) ($fields[$template_l] ?? '')) : '';

                    if ($categoryname_l and $fields[$categoryname_l]) {
                        if ($categoryid = $this->get_categoryid($fields[$categoryname_l], $trace)) {
                            $course->category = $categoryid;
                        } else {
                            // Bad luck, better not continue because unwanted ppl might get access to course in different category.
                            $trace->output("error: invalid category path '".$fields[$categoryname_l]."', can not create course: ".
                                           $fields[$shortname_l], 1);
                            continue;
                        }
                    } else if ($category and $fields[$category_l]) {
                        // Old-style category lookup using localcategoryfield.
                        if ($coursecategory = $DB->get_record('course_categories',
                                [$localcategoryfield => $fields[$category_l]])) {
                            // Yay, correctly specified category!
                            $course->category = $coursecategory->id;
                        } else {
                            // Bad luck, better not continue because unwanted ppl might get access to course in different category.
                            $trace->output('error: invalid category '.$localcategoryfield.', can not create course: '.$fields[$shortname_l], 1);
                            continue;
                        }
                    } else {
                        $course->category = $defaultcategory->id;
                    }

                    if ($startdate) {
                        if (!empty($fields[$startdatelowercased])) {
                            $course->startdate = is_number($fields[$startdatelowercased])
                                ? $fields[$startdatelowercased]
                                : strtotime($fields[$startdatelowercased]);

                            // Broken start date. Stop syncing this course.
                            if ($course->startdate === false) {
                                $trace->output('error: invalid external course start date value: ' . json_encode($fields), 1);
                                continue;
                            }
                        }
                    }

                    if ($enddate) {
                        if (!empty($fields[$enddatelowercased])) {
                            $course->enddate = is_number($fields[$enddatelowercased])
                                ? $fields[$enddatelowercased]
                                : strtotime($fields[$enddatelowercased]);

                            // Broken end date. Stop syncing this course.
                            if ($course->enddate === false) {
                                $trace->output('error: invalid external course end date value: ' . json_encode($fields), 1);
                                continue;
                            }
                        }
                    }

                    $createcourses[] = $course;
                }
            }
            $rs->Close();
        } else {
            $extdb->Close();
            $trace->output('Error reading data from the external course table');
            $trace->finished();
            return 4;
        }
        if ($createcourses) {
            require_once("$CFG->dirroot/course/lib.php");

            $templatecourse = $this->get_config('templatecourse');

            // Field that identifies the template of each course ('Local template field' setting). It is used as a column
            // name, so only the values offered by the setting are accepted, and its default is used if it is not set.
            $localtemplatefield = $this->get_config('localtemplatefield');
            if (!in_array($localtemplatefield, ['id', 'idnumber', 'shortname'], true)) {
                $localtemplatefield = 'idnumber';
            }

            $defaulttemplate = false;
            $defaulttemplateid = 0;
            if ($templatecourse) {
                if ($defaulttemplate = $DB->get_record('course', ['shortname' => $templatecourse])) {
                    $defaulttemplate = fullclone(course_get_format($defaulttemplate)->get_course());
                    $defaulttemplateid = $defaulttemplate->id;
                    if (!isset($defaulttemplate->numsections)) {
                        $defaulttemplate->numsections = course_get_format($defaulttemplate)->get_last_section_number();
                    }
                    unset($defaulttemplate->id);
                    unset($defaulttemplate->fullname);
                    unset($defaulttemplate->shortname);
                    unset($defaulttemplate->idnumber);
                    unset($defaulttemplate->summary);
                    unset($defaulttemplate->category);
                } else {
                    $trace->output("can not find template for new course!", 1);
                }
            }
            if (!$defaulttemplate) {
                $defaulttemplate = new stdClass();
                $defaulttemplate->summary = '';
                $defaulttemplate->summaryformat  = FORMAT_HTML;
                $defaulttemplate->format         = $courseconfig->format;
                $defaulttemplate->numsections    = $courseconfig->numsections;
                $defaulttemplate->newsitems      = $courseconfig->newsitems;
                $defaulttemplate->showgrades     = $courseconfig->showgrades;
                $defaulttemplate->showreports    = $courseconfig->showreports;
                $defaulttemplate->showactivitydates = $courseconfig->showactivitydates;
                $defaulttemplate->maxbytes       = $courseconfig->maxbytes;
                $defaulttemplate->groupmode      = $courseconfig->groupmode;
                $defaulttemplate->groupmodeforce = $courseconfig->groupmodeforce;
                $defaulttemplate->visible        = $courseconfig->visible;
                $defaulttemplate->lang           = $courseconfig->lang;
                $defaulttemplate->enablecompletion = $courseconfig->enablecompletion;
                $defaulttemplate->startdate      = usergetmidnight(time());
                if ($courseconfig->courseenddateenabled) {
                    $defaulttemplate->enddate    = usergetmidnight(time()) + $courseconfig->courseduration;
                }
            }

            foreach ($createcourses as $fields) {
                $templateid = $defaulttemplateid;
                if ($fields->template) {
                    if ($coursetemplate = $DB->get_record('course', [$localtemplatefield => $fields->template])) {
                        $newcourse = fullclone(course_get_format($coursetemplate)->get_course());
                        $templateid = $newcourse->id;
                        unset($newcourse->id);
                        unset($newcourse->fullname);
                        unset($newcourse->shortname);
                        unset($newcourse->idnumber);
                        unset($newcourse->summary);
                        unset($newcourse->category);
                    } else {
                        $newcourse = clone($defaulttemplate);
                        $trace->output('can not find template for new course! Using default template.', 1);
                    }
                } else {
                    $newcourse = clone($defaulttemplate);
                }

                $newcourse->fullname  = $fields->fullname;
                $newcourse->shortname = $fields->shortname;
                $newcourse->idnumber  = $fields->idnumber;
                $newcourse->summary   = $fields->summary;
                $newcourse->category  = $fields->category;

                if (isset($fields->startdate)) {
                    $newcourse->startdate = $fields->startdate;
                }

                if (isset($fields->enddate)) {
                    // Validating end date.
                    if ($fields->enddate > 0 && $newcourse->startdate > $fields->enddate) {
                        $trace->output(
                            "can not insert new course, the end date must be after the start date: " . $newcourse->shortname, 1
                        );
                        continue;
                    }
                    $newcourse->enddate = $fields->enddate;
                } else {
                    if ($courseconfig->courseenddateenabled) {
                        $newcourse->enddate = $newcourse->startdate + $courseconfig->courseduration;
                    }
                }

                // Detect duplicate data once again, above we can not find duplicates
                // in external data using DB collation rules...
                if ($DB->record_exists('course', array('shortname' => $newcourse->shortname))) {
                    $trace->output("can not insert new course, duplicate shortname detected: ".$newcourse->shortname, 1);
                    continue;
                } else if (!empty($newcourse->idnumber) and $DB->record_exists('course', array('idnumber' => $newcourse->idnumber))) {
                    $trace->output("can not insert new course, duplicate idnumber detected: ".$newcourse->idnumber, 1);
                    continue;
                }
                $trace->output("creating course: $newcourse->idnumber, $newcourse->fullname, $newcourse->shortname, ".
                               "$newcourse->summary, $newcourse->category", 1);

                if ($templateid) {
                    // If we have a real template (i.e., based on an existing course) duplicate it (including all activities,
                    // blocks and filters, but no users nor groups) and update the resulting course with the new course fields.

                    // Some parts of the restore use the current user, so run it as admin through the session manager,
                    // which keeps the global and the session user in sync, and drop it as soon as it is not needed.
                    global $USER;
                    $olduser = $USER;
                    \core\session\manager::set_user(get_admin());
                    try {
                        // Templates are usually hidden, so use the site default visibility instead of the template's one.
                        $newcourseid = $this->duplicate_template_course($templateid, $newcourse->fullname,
                                $newcourse->shortname, $newcourse->category, empty($courseconfig->visible) ? 0 : 1);

                        $updatecourses = [[
                            'id' => $newcourseid,
                            'idnumber' => $newcourse->idnumber,
                            'summary' => $newcourse->summary,
                        ]];
                        // The duplicate keeps the dates of the template, apply the external ones if there are any.
                        // They go in a separate update so that invalid dates do not prevent setting the idnumber.
                        if (isset($fields->startdate) || isset($fields->enddate)) {
                            $dates = ['id' => $newcourseid, 'startdate' => $newcourse->startdate];
                            if (isset($newcourse->enddate)) {
                                $dates['enddate'] = $newcourse->enddate;
                            }
                            $updatecourses[] = $dates;
                        }
                        foreach ($updatecourses as $updatecourse) {
                            try {
                                update_course((object) $updatecourse);
                            } catch (moodle_exception $e) {
                                $trace->output('error: could not update course ' . $newcourse->shortname . ': '
                                        . $e->getMessage(), 1);
                            }
                        }
                    } catch (\Throwable $e) {
                        // Log the failure and go on with the next course. An incomplete course has already been removed,
                        // so it will be created again in the next synchronisation.
                        $trace->output('error: could not create course ' . $newcourse->shortname . ' from template '
                                . $templateid . ': ' . $e->getMessage(), 1);
                    } finally {
                        // Drop the elevated privileges even if the duplication fails.
                        \core\session\manager::set_user($olduser);
                    }
                } else {
                    // Course creation without a template.
                    create_course($newcourse);
                }
            }

            unset($createcourses);
            unset($defaulttemplate);
        }

        // Close db connection.
        $extdb->Close();

        $trace->output('...course synchronisation finished.');
        $trace->finished();

        return 0;
    }

    /**
     * Creates a new course as a copy of a template course, without users, groups nor groupings.
     *
     * It works as core_course_external::duplicate_course(), with the same backup settings, but it uses the backup and
     * restore API directly: the capabilities are checked for the admin user and the groups are not copied at all.
     * If the copy fails, the new course is removed, so that it does not block its shortname.
     *
     * @param int $templateid id of the template course.
     * @param string $fullname full name of the new course.
     * @param string $shortname short name of the new course.
     * @param int $categoryid category of the new course.
     * @param int $visible visibility of the new course.
     * @return int id of the new course.
     * @throws moodle_exception if the copy fails.
     */
    protected function duplicate_template_course(int $templateid, string $fullname, string $shortname, int $categoryid,
            int $visible): int {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $userid = get_admin()->id;
        // The defaults of core_course_external::duplicate_course(), plus no groups and groupings.
        $settings = [
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1,
            'users' => 0,
            'enrolments' => backup::ENROL_WITHUSERS,
            'role_assignments' => 0,
            'comments' => 0,
            'userscompletion' => 0,
            'logs' => 0,
            'grade_histories' => 0,
            'groups' => 0,
        ];

        $bc = null;
        $file = null;
        $backupbasepath = null;
        $newcourseid = null;
        try {
            $bc = new backup_controller(backup::TYPE_1COURSE, $templateid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO,
                backup::MODE_SAMESITE, $userid);
            foreach ($settings as $name => $value) {
                if ($bc->get_plan()->setting_exists($name)) {
                    $bc->get_plan()->get_setting($name)->set_value($value);
                }
            }
            $backupid = $bc->get_backupid();
            $backupbasepath = $bc->get_plan()->get_basepath();
            $bc->execute_plan();
            $file = $bc->get_results()['backup_destination'];
            $bc->destroy();
            $bc = null;

            if (!file_exists($backupbasepath . '/moodle_backup.xml')) {
                $file->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), $backupbasepath);
            }

            $newcourseid = restore_dbops::create_new_course($fullname, $shortname, $categoryid);
            $this->restore_template_backup($backupid, $newcourseid, $userid, $settings);

            // The restore sets the names and the visibility of the template, set the ones of the new course.
            $DB->update_record('course', (object) ['id' => $newcourseid, 'fullname' => $fullname,
                'shortname' => $shortname, 'visible' => $visible]);
            rebuild_course_cache($newcourseid, true);
        } catch (\Throwable $e) {
            // Remove the incomplete course, so that it is created again in the next synchronisation.
            if ($newcourseid and $course = $DB->get_record('course', ['id' => $newcourseid])) {
                try {
                    delete_course($course, false);
                } catch (\Throwable $deleteerror) {
                    debugging('Could not remove the incomplete course ' . $shortname . ': ' . $deleteerror->getMessage(),
                        DEBUG_DEVELOPER);
                }
            }
            throw $e;
        } finally {
            if ($bc) {
                $bc->destroy();
            }
            if ($backupbasepath and empty($CFG->keeptempdirectoriesonbackup)) {
                fulldelete($backupbasepath);
            }
            if ($file) {
                $file->delete();
            }
        }

        return $newcourseid;
    }

    /**
     * Restores the backup of a template course into a new course, as part of duplicate_template_course().
     *
     * @param string $backupid id of the backup of the template course.
     * @param int $newcourseid id of the new, empty course.
     * @param int $userid id of the user that runs the restore.
     * @param array $settings restore settings, as setting name => value.
     * @return void
     * @throws moodle_exception if the restore fails.
     */
    protected function restore_template_backup(string $backupid, int $newcourseid, int $userid, array $settings): void {
        $rc = new restore_controller($backupid, $newcourseid, backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $userid,
            backup::TARGET_NEW_COURSE);
        try {
            foreach ($settings as $name => $value) {
                if ($rc->get_plan()->setting_exists($name)) {
                    $setting = $rc->get_plan()->get_setting($name);
                    if ($setting->get_status() == backup_setting::NOT_LOCKED) {
                        $setting->set_value($value);
                    }
                }
            }
            if (!$rc->execute_precheck()) {
                $precheckresults = $rc->get_precheck_results();
                if (!empty($precheckresults['errors'])) {
                    throw new moodle_exception('backupprecheckerrors', 'webservice', '',
                        implode(' ', $precheckresults['errors']));
                }
            }
            $rc->execute_plan();
        } finally {
            $rc->destroy();
        }
    }

    /**
     * It synchronizes user groups with external database,
     * creating new ones if necessary.
     * Groups can be added to groupings if defined, and if groupingcreation
     * setting is enabled defined new groupings are created.
     *
     * @param progress_trace $trace
     * @return int 0 means success, 1 db connect failure, 4 db read failure
     * @throws dml_exception
     */
    public function sync_groups(progress_trace $trace) {
        global $CFG, $DB;

        if (!$this->get_config('dbtype') or !$this->get_config('newgrouptable') or
                !$this->get_config('newgroupname') or !$this->get_config('newgroupidnumber') or
                !$this->get_config('newgroupcourse')) {
            $trace->output('Groups synchronisation skipped.');
            $trace->finished();
            return 0;
        }

        $trace->output("\nStarting group synchronisation...");

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return 1;
        }

        $table = trim($this->get_config('newgrouptable'));
        $name = trim($this->get_config('newgroupname'));
        $idnumber = trim($this->get_config('newgroupidnumber'));
        // The optional settings may not be saved yet, so they are cast to string.
        $description = trim((string) $this->get_config('newgroupdesc'));
        $course = trim($this->get_config('newgroupcourse'));
        $grouping = trim((string) $this->get_config('newgroupgroupings'));
        $groupingcreation = $this->get_config('groupingcreation');
        $messaging = $this->get_config('groupmessaging');
        $localcoursefield = trim($this->get_config('localcoursefield'));

        $namelow = strtolower($name);
        $idnumberlow = strtolower($idnumber);
        $courselow = strtolower($course);
        $descriptionlow = strtolower($description);
        $groupinglow = strtolower($grouping);

        $extdbgroups = [];
        $dbgroups = [];

        // External db groups.
        $sqlfields = [$name, $idnumber, $course];
        if ($description) {
            $sqlfields[] = $description;
        }
        if ($grouping) {
            $sqlfields[] = $grouping;
        }
        [$sql, $extparams] = $this->db_get_query($extdb, $table, [], $sqlfields, true);
        if ($rs = $extdb->Execute($sql, $extparams)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    // The column names are compared in lowercase, whatever case the external database returns.
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);
                    if (empty($fields[$namelow]) or empty($fields[$idnumberlow]) or empty($fields[$courselow])) {
                        $trace->output('error: invalid external group record, name, id number and course are mandatory:
                         '.json_encode($fields), 1);
                        continue;
                    }
                    // Course identifier and group idnumber, separated so that different pairs never share a key.
                    $extdbgroups[$fields[$courselow] . "\0" . $fields[$idnumberlow]] = $fields;
                }
            }
            $rs->Close();
        } else {
            $extdb->Close();
            $trace->output('Error reading data from the external groups table');
            $trace->finished();
            return 4;
        }

        // Moodle db groups (existing groups), with the same keys as the external ones.
        $dbsql = "SELECT g.id, g.courseid, g.idnumber, g.description, c.$localcoursefield AS coursekey
                    FROM {groups} g
                    JOIN {course} c ON c.id = g.courseid
                   WHERE g.idnumber <> ''";
        $rs = $DB->get_recordset_sql($dbsql);
        foreach ($rs as $dbgroup) {
            $dbgroups[$dbgroup->coursekey . "\0" . $dbgroup->idnumber] = $dbgroup;
        }
        $rs->close();

        require_once("$CFG->dirroot/group/lib.php");

        // Existing groups: if the field is configured, the description follows the external table. The name is not
        // updated, as teachers may rename the groups, and an empty external description keeps the one in Moodle.
        if ($description) {
            foreach (array_intersect_key($extdbgroups, $dbgroups) as $key => $group) {
                $dbgroup = $dbgroups[$key];
                $newdescription = (string) ($group[$descriptionlow] ?? '');
                if ($newdescription === '' or $newdescription === (string) $dbgroup->description) {
                    continue;
                }
                $update = new stdClass();
                $update->id = $dbgroup->id;
                $update->courseid = $dbgroup->courseid;
                $update->description = $newdescription;
                // Keep the group messaging as it is: groups_update_group() disables the group conversation if not given.
                $update->enablemessaging = (int) $DB->get_field('message_conversations', 'enabled',
                    ['component' => 'core_group', 'itemtype' => 'groups', 'itemid' => $dbgroup->id]);
                $trace->output("  updating description of group " . $dbgroup->idnumber . " in course " . $group[$courselow]
                               . ": '" . $newdescription . "'");
                groups_update_group($update);
            }
        }

        // Only the external groups that do not exist yet in their course have to be created.
        $creategroups = array_diff_key($extdbgroups, $dbgroups);
        unset($dbgroups);

        if ($creategroups) {
            foreach ($creategroups as $group) {
                // The course to which the group will belong must exist.
                $courseid = $DB->get_field('course', 'id', [$localcoursefield => $group[$courselow]]);
                if (!$courseid) {
                    $trace->output("  [x] Course " . $localcoursefield . " " . $group[$courselow] . " to which the group ID number "
                                   . $group[$idnumberlow] ." should belong doesn't exist, group will not be created.");
                    continue;
                }

                $courseid = $DB->get_field_sql("SELECT id
                                                  FROM {course}
                                                 WHERE shortname="."'".$group[$courselow]."'");
                if ($DB->record_exists('groups', array('idnumber' => $group[$idnumberlow], 'courseid' => $courseid))) {
                    // Already exists, skip.
                    //$trace->output("[x] Skipping group name ".$group[$namelow].", it already exists in course shortname "
                    //               .$group[$courselow].", but with a different name.");
                    continue;
                }

                $newgroup = new stdClass();
                $newgroup->courseid = $courseid;
                $newgroup->name = $group[$namelow];
                $newgroup->idnumber = $group[$idnumberlow];
                $newgroup->description = $description ? ($group[$descriptionlow] ?? '') : '';
                $newgroup->course = $group[$courselow];
                $newgroup->enablemessaging = $messaging;

                $trace->output("  creating group: $newgroup->name, $newgroup->idnumber, $newgroup->description, in course "
                .$newgroup->course);
                $newgroupid = groups_create_group($newgroup);

                if ($grouping && !empty($group[$groupinglow])) {
                    $existinggrouping = $DB->get_record('groupings',
                        ['idnumber' => $group[$groupinglow], 'courseid' => $courseid], 'id');
                    if ($existinggrouping) {
                        groups_assign_grouping($existinggrouping->id, $newgroupid);
                    } else if ($groupingcreation) {
                        $newgrouping = new stdClass();
                        $newgrouping->idnumber = $group[$groupinglow];
                        $newgrouping->courseid = $courseid;
                        $newgrouping->name = $group[$groupinglow];
                        $newgroupingid = groups_create_grouping($newgrouping);

                        groups_assign_grouping($newgroupingid, $newgroupid);
                    } else {
                        $trace->output("  [x] Grouping ID number " . $group[$groupinglow] . " not found in course "
                                       . $group[$courselow] . " (and not creating groupings), group "
                                       . $group[$idnumberlow] . " will not be added to it.");
                    }
                }
            }
            unset($creategroups);
        }

        // Close db connection.
        $extdb->Close();

        $trace->output('...group synchronisation finished.');
        $trace->finished();
        return 0;
    }

    /**
     * Whether all the settings needed by the group enrolments synchronisation are set.
     *
     * The course of each row of the group enrolments table is read from the field set in the 'newgroupcourse'
     * setting, so it is needed as well, even if no new groups are created.
     *
     * @return bool
     */
    protected function is_group_enrolment_sync_configured(): bool {
        foreach (['dbtype', 'groupenroltable', 'userfield', 'groupfield', 'newgroupcourse'] as $setting) {
            if (trim((string) $this->get_config($setting)) === '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Forces synchronisation of group enrolments with external database,
     *
     * @param progress_trace $trace
     * @return int 0 means success, 1 db connect failure, 4 db read failure
     */
    public function sync_group_enrolments(progress_trace $trace) {
        if (!$this->is_group_enrolment_sync_configured()) {
            $trace->output('Group enrolments synchronisation skipped.');
            $trace->finished();
            return 0;
        }

        $trace->output("\nStarting group enrolment synchronisation...");

        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return 1;
        }

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $result = $this->sync_group_memberships($extdb, $trace);

        // Close db connection.
        $extdb->Close();

        $trace->output("...group enrolment synchronisation finished.\n");
        $trace->finished();
        return $result;
    }

    /**
     * Synchronises the group memberships with the external group enrolments table.
     *
     * The external table is read once through the external database connection and matched in PHP with the Moodle
     * users, courses, groups and group members, so that any external database type is supported.
     *
     * - Users in the external table that are not members of the group yet are added to it.
     * - If groupupgrading is enabled, manually added members (empty component) are converted to enrol_database members.
     * - Members added by enrol_database that are not in the external table anymore are removed from the group.
     *
     * @param ADOConnection $extdb connection to the external database.
     * @param progress_trace $trace
     * @param stdClass|null $user only synchronise the memberships of this user, all of them when null.
     * @return int 0 means success, 4 db read failure
     */
    protected function sync_group_memberships(ADOConnection $extdb, progress_trace $trace, ?stdClass $user = null): int {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/group/lib.php');

        $table = trim($this->get_config('groupenroltable'));
        $userfield = trim($this->get_config('userfield'));
        $groupcoursefield = trim($this->get_config('newgroupcourse'));
        $groupfield = trim($this->get_config('groupfield'));
        $localuserfield = trim($this->get_config('localuserfield'));
        $localcoursefield = trim($this->get_config('localcoursefield'));
        $groupupgrading = $this->get_config('groupupgrading');

        $userfieldlow = strtolower($userfield);
        $groupcoursefieldlow = strtolower($groupcoursefield);
        $groupfieldlow = strtolower($groupfield);

        $conditions = [];
        $userselect = '';
        $userparams = [];
        if ($user) {
            if (!empty($user->deleted) or !isset($user->$localuserfield) or (string) $user->$localuserfield === '') {
                return 0;
            }
            // The filter is applied by the external database, so only the rows of this user are transferred.
            $conditions[$userfield] = $user->$localuserfield;
            $userselect = 'AND gm.userid = :userid';
            $userparams['userid'] = $user->id;
        }

        // Memberships added after this moment may come from newer external data (e.g. the login of a user while the
        // scheduled task is running), so they are not removed by this run.
        $synctime = time();

        // External memberships, grouped as course => group => list of users.
        [$sql, $extparams] = $this->db_get_query($extdb, $table, $conditions, [$userfield, $groupcoursefield, $groupfield], true);
        if (!$rs = $extdb->Execute($sql, $extparams)) {
            $trace->output('Error reading data from the external group enrolments table');
            return 4;
        }
        $extmembers = [];
        $extusers = [];
        while ($fields = $rs->FetchRow()) {
            // The column names are compared in lowercase, whatever case the external database returns.
            $fields = array_change_key_case($fields, CASE_LOWER);
            $fields = $this->db_decode($fields);
            $extuser = (string) ($fields[$userfieldlow] ?? '');
            $extcourse = (string) ($fields[$groupcoursefieldlow] ?? '');
            $extgroup = (string) ($fields[$groupfieldlow] ?? '');
            if ($extuser === '' or $extcourse === '' or $extgroup === '') {
                continue;
            }
            $extmembers[$extcourse][$extgroup][] = $extuser;
            $extusers[$extuser] = true;
        }
        $rs->Close();

        // An empty external table (or one without any valid row) in a full synchronisation is more likely a problem of
        // the external data (e.g. the table is being reloaded) or of the settings than a request to empty all the
        // groups, so nothing is removed. The synchronisation of a single user may legitimately have no rows.
        if (!$user and !$extmembers) {
            $trace->output('The external group enrolments table has no valid rows, no group members are removed.');
            return 0;
        }

        // Moodle users and courses matching the external values, and the groups of those courses.
        if ($user) {
            // Only this user, even if other users share the same value in the local user field.
            $userids = [(string) $user->$localuserfield => [$user->id]];
        } else {
            $userids = $this->get_ids_by_field('user', $localuserfield, array_keys($extusers), 'deleted = 0');
        }
        $courseids = $this->get_ids_by_field('course', $localcoursefield, array_keys($extmembers));
        unset($extusers);
        $usernames = [];
        foreach ($userids as $value => $ids) {
            foreach ($ids as $id) {
                $usernames[$id] = $value;
            }
        }
        $groupids = [];
        $groupinfo = [];
        foreach (array_chunk(array_unique(array_merge([], ...array_values($courseids))), 1000) as $chunk) {
            [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED);
            // The memberships are linked to the enrol_database instance of the course, if there is one.
            $instanceids = $DB->get_records_sql_menu("SELECT courseid, MIN(id)
                                                        FROM {enrol}
                                                       WHERE enrol = 'database' AND courseid $insql
                                                    GROUP BY courseid", $params);
            $groups = $DB->get_recordset_sql("SELECT id, courseid, idnumber
                                                FROM {groups}
                                               WHERE courseid $insql AND idnumber <> ''", $params);
            foreach ($groups as $group) {
                $group->itemid = (int) ($instanceids[$group->courseid] ?? 0);
                $groupids[$group->courseid][$group->idnumber] = $group->id;
                $groupinfo[$group->id] = $group;
            }
            $groups->close();
        }

        // Memberships requested by the external table, as groupid => userid => true.
        $requested = [];
        foreach ($extmembers as $extcourse => $extgroups) {
            foreach ($courseids[$extcourse] ?? [] as $courseid) {
                foreach ($extgroups as $extgroup => $extgroupusers) {
                    if (!isset($groupids[$courseid][$extgroup])) {
                        continue;
                    }
                    $groupid = $groupids[$courseid][$extgroup];
                    foreach ($extgroupusers as $extuser) {
                        foreach ($userids[$extuser] ?? [] as $userid) {
                            $requested[$groupid][$userid] = true;
                        }
                    }
                }
            }
        }
        unset($extmembers, $userids, $courseids, $groupids);

        // Existing memberships of the requested groups: they are marked as existing, and the manually added ones (if
        // groupupgrading is enabled) and the ones of this plugin not linked to the enrol instance of the course are
        // collected to be updated.
        $updates = [];
        foreach (array_chunk(array_keys($requested), 1000) as $chunk) {
            [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED);
            $members = $DB->get_recordset_sql("SELECT gm.id, gm.groupid, gm.userid, gm.component, gm.itemid
                                                 FROM {groups_members} gm
                                                WHERE gm.groupid $insql $userselect", $params + $userparams);
            foreach ($members as $member) {
                if (!isset($requested[$member->groupid][$member->userid])) {
                    continue;
                }
                $requested[$member->groupid][$member->userid] = false;
                $itemid = $groupinfo[$member->groupid]->itemid;
                if (($member->component === '' and $groupupgrading) or
                        ($member->component === 'enrol_database' and (int) $member->itemid !== $itemid)) {
                    $updates[] = $member;
                }
            }
            $members->close();
        }

        // Add the users to their groups.
        foreach ($requested as $groupid => $members) {
            $group = $groupinfo[$groupid];
            $groupurl = $CFG->wwwroot . "/group/overview.php?id=" . $group->courseid . "&group=" . $groupid;
            foreach ($members as $userid => $add) {
                if (!$add) {
                    continue;
                }
                if ($this->add_group_member($groupid, $userid, $group->itemid)) {
                    $trace->output("  adding user " . $usernames[$userid] . " to group: " . $group->idnumber .
                                   ": " . $groupurl);
                } else {
                    $trace->output("  error adding user " . $usernames[$userid] . " to group " . $group->idnumber .
                                   ", not enrolled in course ID " . $group->courseid . ": " . $groupurl);
                }
            }
        }

        // Take over the manually added members and link the members to the enrol instance of the course. The membership
        // itself does not change, so it is updated in place: no group membership events are triggered.
        foreach ($updates as $member) {
            $group = $groupinfo[$member->groupid];
            if ($member->component === '') {
                $trace->output("  upgrading membership of user " . $usernames[$member->userid] . " in group " .
                               $group->idnumber . " to enrol_database: " . $CFG->wwwroot . "/group/overview.php?id=" .
                               $group->courseid . "&group=" . $member->groupid);
            }
            $DB->update_record('groups_members',
                (object) ['id' => $member->id, 'component' => 'enrol_database', 'itemid' => $group->itemid]);
        }
        unset($updates);

        // Remove the members added by this plugin that are not in the external table anymore.
        $members = $DB->get_recordset_sql("SELECT gm.id, gm.groupid, gm.userid, u.$localuserfield AS localuserfield,
                                                  g.idnumber, g.courseid
                                             FROM {groups_members} gm
                                             JOIN {user} u ON u.id = gm.userid
                                             JOIN {groups} g ON g.id = gm.groupid
                                            WHERE gm.component = 'enrol_database'
                                              AND gm.timeadded < :synctime
                                              AND u.deleted = 0
                                              $userselect", ['synctime' => $synctime] + $userparams);
        $removals = [];
        foreach ($members as $member) {
            if (!isset($requested[$member->groupid][$member->userid])) {
                $removals[] = $member;
            }
        }
        $members->close();
        unset($requested);
        foreach ($removals as $member) {
            $trace->output("  removing user " . $member->localuserfield . " from group " . $member->idnumber . ": " .
                           $CFG->wwwroot . "/group/overview.php?id=" . $member->courseid . "&group=" . $member->groupid);
            groups_remove_member($member->groupid, $member->userid);
        }

        return 0;
    }

    /**
     * Adds a user to a group as a member managed by this plugin.
     *
     * The scheduled task and the login of the user may try to add the same member at the same time. In that case
     * the insert of one of them fails because of the unique key of the group members table, and the member is already
     * there, so it is not an error.
     *
     * @param int $groupid
     * @param int $userid
     * @param int $itemid id of the enrol_database instance of the course, 0 if there is none.
     * @return bool true if the user is a member of the group, false if the user can not be added (e.g. not enrolled).
     */
    protected function add_group_member(int $groupid, int $userid, int $itemid): bool {
        global $DB;
        try {
            return groups_add_member($groupid, $userid, 'enrol_database', $itemid);
        } catch (dml_write_exception $e) {
            if (!$DB->is_transaction_started() and groups_is_member($groupid, $userid)) {
                return true;
            }
            throw $e;
        }
    }

    /**
     * Returns the ids of the records of a Moodle table whose field matches any of the given values.
     *
     * @param string $table Moodle table name, without prefix.
     * @param string $field field to match.
     * @param array $values values to look for.
     * @param string $extraselect additional condition, without parameters.
     * @return array value => list of ids, the same value may match several records.
     */
    protected function get_ids_by_field(string $table, string $field, array $values, string $extraselect = ''): array {
        global $DB;

        $ids = [];
        // Small chunks, so that the number of parameters is supported by all the databases.
        foreach (array_chunk(array_map('strval', $values), 1000) as $chunk) {
            [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED);
            $select = "$field $insql" . ($extraselect ? " AND $extraselect" : '');
            $records = $DB->get_recordset_select($table, $select, $params, '', "id, $field AS value");
            foreach ($records as $record) {
                $ids[(string) $record->value][] = $record->id;
            }
            $records->close();
        }
        return $ids;
    }

    /**
     * Build a SELECT statement to run against the external database, with values escaped into the SQL.
     *
     * @deprecated since Moodle 6.0.
     * @todo MDL-90483 Final deprecation in Moodle 7.0.
     * @param string $table name of the external table.
     * @param array $conditions field name => value pairs forming the WHERE clause.
     * @param array $fields fields to select, all of them when empty.
     * @param bool $distinct whether to select distinct records only.
     * @param string $sort ORDER BY clause contents, none when empty.
     * @return string the SQL statement.
     */
    #[\core\attribute\deprecated(
        replacement: 'enrol_database_plugin::db_get_query',
        since: '6.0',
        mdl: 'MDL-90353',
    )]
    protected function db_get_sql($table, array $conditions, array $fields, $distinct = false, $sort = "") {
        \core\deprecation::emit_deprecation_if_present([self::class, __FUNCTION__]);
        $fields = $fields ? implode(',', $fields) : "*";
        $where = array();
        if ($conditions) {
            foreach ($conditions as $key=>$value) {
                $value = $this->db_encode($this->db_addslashes($value));

                $where[] = "$key = '$value'";
            }
        }
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql = "SELECT $distinct $fields
                  FROM $table
                 $where
                  $sort";

        return $sql;
    }

    /**
     * Build a parameterised SELECT statement to run against the external database.
     *
     * Placeholders are generated by the connection so that each ADOdb driver gets the syntax it binds natively
     * (for example "?" for MySQL and MSSQL, "$1" for PostgreSQL and ":name" for Oracle oci8).
     *
     * @param ADOConnection $extdb connection the statement will be executed on.
     * @param string $table name of the external table.
     * @param array $conditions field name => value pairs forming the WHERE clause.
     * @param array $fields fields to select, all of them when empty.
     * @param bool $distinct whether to select distinct records only.
     * @param string $sort ORDER BY clause contents, none when empty.
     * @return array [string $sql, array $params] the statement and the named values to bind to it, in placeholder order.
     */
    protected function db_get_query(ADOConnection $extdb, $table, array $conditions, array $fields, $distinct = false, $sort = "") {
        $fields = $fields ? implode(',', $fields) : "*";
        $where = [];
        $params = [];
        // Restart placeholder numbering for drivers that number them (e.g. PostgreSQL "$1", "$2").
        $extdb->Param(false);
        foreach ($conditions as $key => $value) {
            $name = 'p' . count($params);
            $where[] = "$key = " . $extdb->Param($name);
            $params[$name] = $this->db_encode($value);
        }
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql = "SELECT $distinct $fields
                  FROM $table
                 $where
                  $sort";

        return [$sql, $params];
    }

    /**
     * Tries to make connection to the external database.
     *
     * @return null|ADONewConnection
     */
    protected function db_init() {
        global $CFG;

        require_once($CFG->libdir.'/adodb/adodb.inc.php');

        // Connect to the external database (forcing new connection).
        $extdb = ADONewConnection($this->get_config('dbtype'));
        if ($this->get_config('debugdb')) {
            $extdb->debug = true;
            ob_start(); // Start output buffer to allow later use of the page headers.
        }

        // The dbtype my contain the new connection URL, so make sure we are not connected yet.
        if (!$extdb->IsConnected()) {
            $result = $extdb->Connect($this->get_config('dbhost'), $this->get_config('dbuser'), $this->get_config('dbpass'), $this->get_config('dbname'), true);
            if (!$result) {
                return null;
            }
        }

        $extdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($this->get_config('dbsetupsql')) {
            $extdb->Execute($this->get_config('dbsetupsql'));
        }
        return $extdb;
    }

    /**
     * Add slashes, we can not use placeholders or system functions.
     *
     * @deprecated since Moodle 5.2.
     * @todo MDL-90483 Final deprecation in Moodle 7.0.
     * @param string $text
     * @return string
     */
    #[\core\attribute\deprecated(
        replacement: 'parameterised queries',
        since: '5.2',
        mdl: 'MDL-90353',
    )]
    protected function db_addslashes($text) {
        \core\deprecation::emit_deprecation_if_present([self::class, __FUNCTION__]);
        // Use custom made function for now - it is better to not rely on adodb or php defaults.
        if ($this->get_config('dbsybasequoting')) {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(array('\'', '"', "\0"), array('\\\'', '\\"', '\\0'), $text);
        } else {
            $text = str_replace("'", "''", $text);
        }
        return $text;
    }

    protected function db_encode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_encode($value);
            }
            return $text;
        } else {
            return core_text::convert($text, 'utf-8', $dbenc);
        }
    }

    protected function db_decode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_decode($value);
            }
            return $text;
        } else {
            return core_text::convert($text, $dbenc, 'utf-8');
        }
    }

    /**
     * Automatic enrol sync executed during restore.
     * @param stdClass $course course record
     */
    public function restore_sync_course($course) {
        $trace = new null_progress_trace();
        $this->sync_enrolments($trace, $course->id);
    }

    /**
     * Restore instance and map settings.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $course
     * @param int $oldid
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB;

        if ($instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>$this->get_name()))) {
            $instanceid = $instance->id;
        } else {
            $instanceid = $this->add_instance($course);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }

    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $oldinstancestatus
     * @param int $userid
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        global $DB;

        if ($this->get_config('unenrolaction') == ENROL_EXT_REMOVED_UNENROL) {
            // Enrolments were already synchronised in restore_instance(), we do not want any suspended leftovers.
            return;
        }
        if (!$DB->record_exists('user_enrolments', array('enrolid'=>$instance->id, 'userid'=>$userid))) {
            $this->enrol_user($instance, $userid, null, 0, 0, ENROL_USER_SUSPENDED);
        }
    }

    /**
     * Restore role assignment.
     *
     * @param stdClass $instance
     * @param int $roleid
     * @param int $userid
     * @param int $contextid
     */
    public function restore_role_assignment($instance, $roleid, $userid, $contextid) {
        if ($this->get_config('unenrolaction') == ENROL_EXT_REMOVED_UNENROL or $this->get_config('unenrolaction') == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
            // Role assignments were already synchronised in restore_instance(), we do not want any leftovers.
            return;
        }
        role_assign($roleid, $userid, $contextid, 'enrol_'.$this->get_name(), $instance->id);
    }

    /**
     * Test plugin settings, print info to output.
     */
    public function test_settings() {
        global $CFG, $OUTPUT;

        // NOTE: this is not localised intentionally, admins are supposed to understand English at least a bit...

        raise_memory_limit(MEMORY_HUGE);

        $this->load_config();

        $enroltable = $this->get_config('remoteenroltable');
        $coursetable = $this->get_config('newcoursetable');

        if (empty($enroltable)) {
            echo $OUTPUT->notification('External enrolment table not specified.', 'notifyproblem');
        }

        if (empty($coursetable)) {
            echo $OUTPUT->notification('External course table not specified.', 'notifyproblem');
        }

        if (empty($coursetable) and empty($enroltable)) {
            return;
        }

        $olddebug = $CFG->debug;
        $olddisplay = ini_get('display_errors');
        ini_set('display_errors', '1');
        $CFG->debug = DEBUG_DEVELOPER;
        $olddebugdb = $this->config->debugdb;
        $this->config->debugdb = 1;
        error_reporting($CFG->debug);

        $adodb = $this->db_init();

        if (!$adodb or !$adodb->IsConnected()) {
            $this->config->debugdb = $olddebugdb;
            $CFG->debug = $olddebug;
            ini_set('display_errors', $olddisplay);
            error_reporting($CFG->debug);
            ob_end_flush();

            echo $OUTPUT->notification('Cannot connect the database.', 'notifyproblem');
            return;
        }

        if (!empty($enroltable)) {
            $rs = $adodb->Execute("SELECT *
                                     FROM $enroltable");
            if (!$rs) {
                echo $OUTPUT->notification('Can not read external enrol table.', 'notifyproblem');

            } else if ($rs->EOF) {
                echo $OUTPUT->notification('External enrol table is empty.', 'notifyproblem');
                $rs->Close();

            } else {
                $columns = array_keys($rs->fetchRow());
                echo $OUTPUT->notification('External enrolment table contains following columns:<br />'.implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        if (!empty($coursetable)) {
            $rs = $adodb->Execute("SELECT *
                                     FROM $coursetable");
            if (!$rs) {
                echo $OUTPUT->notification('Can not read external course table.', 'notifyproblem');

            } else if ($rs->EOF) {
                echo $OUTPUT->notification('External course table is empty.', 'notifyproblem');
                $rs->Close();

            } else {
                $columns = array_keys($rs->fetchRow());
                echo $OUTPUT->notification('External course table contains following columns:<br />'.implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        $usertable = trim((string) $this->get_config('newusertable'));
        if ($this->is_user_creation_misconfigured()) {
            echo $OUTPUT->notification('Users can not be created from the external user table, because no field of it ' .
                'is mapped to the local user field ' . $this->get_config('localuserfield') . '.', 'notifyproblem');
        }
        if ($usertable !== '') {
            $rs = $adodb->Execute("SELECT *
                                     FROM $usertable");
            if (!$rs) {
                echo $OUTPUT->notification('Can not read external user table.', 'notifyproblem');
            } else if ($rs->EOF) {
                echo $OUTPUT->notification('External user table is empty.', 'notifyproblem');
                $rs->Close();
            } else {
                $columns = array_keys($rs->fetchRow());
                echo $OUTPUT->notification('External user table contains following columns:<br />' .
                    implode(', ', $columns), 'notifysuccess');
                $rs->Close();
            }
        }

        $adodb->Close();

        $this->config->debugdb = $olddebugdb;
        $CFG->debug = $olddebug;
        ini_set('display_errors', $olddisplay);
        error_reporting($CFG->debug);
        ob_end_flush();
    }

    /**
     * Removes the manual enrolment of a user in a course when it duplicates the external database enrolment.
     *
     * The manual enrolment is only removed if the user does not have any other role in the course with a higher
     * priority (lower sortorder, directly or through its archetype) than the role assigned by the external database.
     *
     * @param stdClass $course course record, with the id and mapping fields.
     * @param int $userid
     * @param int $roleid role assigned by the external database.
     * @param string $useridentifier identifier of the user in the external database, for the logs.
     * @param progress_trace $trace
     * @return bool true if the manual enrolment has been removed.
     */
    protected function remove_duplicated_manual_enrolment(stdClass $course, int $userid, int $roleid, string $useridentifier,
            progress_trace $trace): bool {
        global $DB;

        $localuserfield = $this->get_config('localuserfield');

        // If the user has other roles in the course, they and their archetypes must have higher sortorders than the
        // external role for the manual enrolment to be removed. Roles without archetype (e.g. custom roles) are
        // compared by their own sortorder only.
        $sql = "SELECT DISTINCT e.id AS enrolid, ctx.id AS contextid
                  FROM {enrol} e
                  JOIN {user_enrolments} ue ON ue.enrolid = e.id
                  JOIN {context} ctx ON ctx.instanceid = e.courseid AND ctx.contextlevel = :courselevel
                  JOIN {role_assignments} ra ON ra.userid = ue.userid AND ra.contextid = ctx.id AND ra.itemid = 0
                 WHERE e.enrol = 'manual'
                   AND ue.userid = :userid
                   AND e.courseid = :courseid
                   AND NOT EXISTS (SELECT 1
                                     FROM {role_assignments} ra2
                                     JOIN {role} r2 ON r2.id = ra2.roleid
                                LEFT JOIN {role} r3 ON r3.shortname = r2.archetype
                                     JOIN {role} externalrole ON externalrole.id = :roleid
                                    WHERE ra2.userid = ue.userid
                                      AND ra2.contextid = ctx.id
                                      AND (r2.sortorder < externalrole.sortorder OR r3.sortorder < externalrole.sortorder)
                                  )";
        $params = ['userid' => $userid, 'courseid' => $course->id, 'roleid' => $roleid, 'courselevel' => CONTEXT_COURSE];
        // A user may be enrolled in more than one manual instance of the course, the first one is removed.
        $manualenrol = $DB->get_record_sql($sql, $params, IGNORE_MULTIPLE);
        if (!$manualenrol) {
            return false;
        }

        $manualinstance = $DB->get_record('enrol', ['id' => $manualenrol->enrolid]);
        if (!$manualinstance) {
            $trace->output("error: Could not find enrol instance for manual enrolment in course $course->mapping.", 1);
            return false;
        }

        role_unassign_all(['contextid' => $manualenrol->contextid, 'userid' => $userid, 'component' => '', 'itemid' => 0]);
        $trace->output("manually assigned roles removed for $localuserfield '$useridentifier' in course $course->mapping.", 1);
        enrol_get_plugin('manual')->unenrol_user($manualinstance, $userid);
        $trace->output("manual enrolment removed for $localuserfield '$useridentifier' in course $course->mapping.", 1);
        return true;
    }

    /**
     * Returns the id of a given course category. If $this->autocreatecategory
     * is set and the category doesn't exist, it creates it and returns the new
     * id. Otherwise it returns false.
     *
     * If $this->categoryseparator is set, it can handle subcategories of
     * any depth. You just need to specify the 'path' of the subcategory
     * as the identifiers of the categories separated by the value of the
     * separator. For example, if we use '/' as the separator, we can
     * specify 'categoryid1/categoryid2/categoryid3' if we are interested
     * in a category identified by 'category3' that is inside a category
     * identified by 'category2' that is inside a category identified by
     * 'category1' that is a top level category.
     *
     * If $this->categoryseparator and $this->autocreate are set, it creates the
     * whole category tree. For example, if we are looking for
     * 'categoryname1/categoryname2/categoryname3', and they don't exist, it will
     * start creating the shallower category (i.e. 'categoryname1'), and it will continue
     * creating categories for deeper categories, building the tree (the next
     * category would be 'categoryname1/categoryname2', and so on).
     *
     * The idnumber of each category is its path, so it can not be longer than the idnumber field (100 characters).
     *
     * @param string $categoryname path of the category, the idnumbers of the categories separated by the separator.
     * @param progress_trace $trace
     * @return int|false id of the category, false if it does not exist and it can not be created.
     */
    public function get_categoryid($categoryname, $trace) {
        global $DB;

        $categoryname = trim((string) $categoryname);
        if ($categoryname === '') {
            // The default category of the plugin settings, or the site default one if it does not exist.
            $defaultcategory = null;
            if ($defaultcategoryid = (int) $this->get_config('defaultcategory')) {
                $defaultcategory = core_course_category::get($defaultcategoryid, IGNORE_MISSING, true);
            }
            return ($defaultcategory ?? core_course_category::get_default())->id;
        }

        $separator = (string) $this->get_config('categoryseparator');
        $autocreate = $this->get_config('autocreatecategory');

        // Names of the categories of the path, from the top level one.
        if ($separator === '') {
            $names = [$categoryname];
        } else {
            if (str_starts_with($categoryname, $separator) || str_ends_with($categoryname, $separator)) {
                $trace->output('error: category name syntax invalid (can not start or end with category separator): '
                        . $categoryname, 1);
                return false;
            }
            $names = array_values(array_filter(array_map('trim', explode($separator, $categoryname)),
                fn(string $name) => $name !== ''));
            if (!$names) {
                $trace->output('error: category name syntax invalid (no category found between separators): '
                        . $categoryname, 1);
                return false;
            }
        }

        // The idnumber of each category is its path with the configured separator, as in the external database.
        $categorypath = implode($separator, $names);
        if (core_text::strlen($categorypath) > 100) {
            $trace->output('error: category path too long (the idnumber can not be longer than 100 characters): '
                    . $categoryname, 1);
            return false;
        }

        if ($dbcat = $DB->get_record('course_categories', ['idnumber' => $categorypath], 'id')) {
            return $dbcat->id;
        }
        if (!$autocreate) {
            // Do not fall back to a parent category: unwanted people might get access to the course.
            $trace->output('error: category [' . $categoryname . '] not found (and not autocreating categories)', 1);
            return false;
        }

        // Create the missing part of the tree, reusing the categories that already exist.
        $parent = 0;
        $path = '';
        foreach ($names as $name) {
            $path .= ($path === '' ? '' : $separator) . $name;
            if ($dbcat = $DB->get_record('course_categories', ['idnumber' => $path], 'id')) {
                $parent = $dbcat->id;
            } else {
                $newcategory = core_course_category::create((object) ['name' => $name, 'idnumber' => $path,
                    'parent' => $parent]);
                $parent = $newcategory->id;
            }
        }
        return $parent;
    }
}

/**
 * Prevents the removal of the group members added by the external database enrolment plugin.
 *
 * @param int $itemid
 * @param int $groupid
 * @param int $userid
 * @return bool
 */
function enrol_database_allow_group_member_remove($itemid, $groupid, $userid) {
    return false;
}
