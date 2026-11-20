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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Database enrolment plugin implementation.
 * @author  Petr Skoda - based on code by Martin Dougiamas, Martin Langhoff and others
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

        $table            = $this->get_config('remoteenroltable');
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

            if (isset($enrols[$courseid])) {
                if ($e = $DB->get_record('user_enrolments', array('userid' => $user->id, 'enrolid' => $instance->id))) {
                    // Reenable enrolment when previously disable enrolment refreshed.
                    if ($e->status == ENROL_USER_SUSPENDED) {
                        $this->update_user_enrol($instance, $user->id, ENROL_USER_ACTIVE);
                    }
                } else {
                    $roleid = reset($enrols[$courseid]);
                    $this->enrol_user($instance, $user->id, $roleid, 0, 0, ENROL_USER_ACTIVE);
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
        if ($ownconnection && !$extdb = $this->db_init()) {
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

        $trace->output('Starting user enrolment synchronisation...');

        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return 1;
        }

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $table            = $this->get_config('remoteenroltable');
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

            // Get current list of enrolled users with their roles.
            $currentroles  = array();
            $currentenrols = array();
            $currentstatus = array();
            $usermapping   = array();
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
                if (!$rs->EOF) {
                    $usersearch = array('deleted' => 0);
                    if ($localuserfield === 'username') {
                        $usersearch['mnethostid'] = $CFG->mnet_localhost_id;
                    }
                    while ($fields = $rs->FetchRow()) {
                        $fields = array_change_key_case($fields, CASE_LOWER);
                        if (empty($fields[$userfield_l])) {
                            $trace->output("error: skipping user without mandatory $localuserfield in course '$course->mapping'", 1);
                            continue;
                        }
                        $mapping = $fields[$userfield_l];
                        if (!isset($usermapping[$mapping])) {
                            $usersearch[$localuserfield] = $mapping;
                            if (!$user = $DB->get_record('user', $usersearch, 'id', IGNORE_MULTIPLE)) {
                                $trace->output("error: skipping unknown user $localuserfield '$mapping' in course '$course->mapping'", 1);
                                continue;
                            }
                            $usermapping[$mapping] = $user->id;
                            $userid = $user->id;
                        } else {
                            $userid = $usermapping[$mapping];
                        }
                        if (empty($fields[$rolefield_l]) or !isset($roles[$fields[$rolefield_l]])) {
                            if (!$defaultrole) {
                                $trace->output("error: skipping user '$userid' in course '$course->mapping' - missing course and default role", 1);
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
                    }
                }
                $rs->Close();
            } else {
                $trace->output("error: skipping course '$course->mapping' - could not match with external database", 1);
                continue;
            }
            unset($usermapping);

            // Enrol all users and sync roles.
            foreach ($requestedenrols as $userid => $userroles) {
                foreach ($userroles as $roleid) {
                    if (empty($currentenrols[$userid])) {
                        $this->enrol_user($instance, $userid, $roleid, 0, 0, ENROL_USER_ACTIVE);
                        $currentroles[$userid][$roleid] = $roleid;
                        $currentenrols[$userid][$roleid] = $roleid;
                        $currentstatus[$userid] = ENROL_USER_ACTIVE;
                        $trace->output("enrolling: $userid ==> $course->shortname as ".$allroles[$roleid]->shortname, 1);
                    }
                }

                // Reenable enrolment when previously disable enrolment refreshed.
                if ($currentstatus[$userid] == ENROL_USER_SUSPENDED) {
                    $this->update_user_enrol($instance, $userid, ENROL_USER_ACTIVE);
                    $trace->output("unsuspending: $userid ==> $course->shortname", 1);
                }
            }

            foreach ($requestedroles as $userid => $userroles) {
                // Assign extra roles.
                foreach ($userroles as $roleid) {
                    if (empty($currentroles[$userid][$roleid])) {
                        role_assign($roleid, $userid, $context->id, 'enrol_database', $instance->id);
                        $currentroles[$userid][$roleid] = $roleid;
                        $trace->output("assigning roles: $userid ==> $course->shortname as ".$allroles[$roleid]->shortname, 1);
                    }
                }

                // Unassign removed roles.
                foreach ($currentroles[$userid] as $cr) {
                    if (empty($userroles[$cr])) {
                        role_unassign($cr, $userid, $context->id, 'enrol_database', $instance->id);
                        unset($currentroles[$userid][$cr]);
                        $trace->output("unsassigning roles: $userid ==> $course->shortname", 1);
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
                        $trace->output("unenrolling: $userid ==> $course->shortname", 1);
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
                        $trace->output("suspending: $userid ==> $course->shortname", 1);
                    }
                    if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                        if (isset($requestedroles[$userid])) {
                            // We want this "other user" to keep their roles.
                            continue;
                        }
                        role_unassign_all(array('contextid'=>$context->id, 'userid'=>$userid, 'component'=>'enrol_database', 'itemid'=>$instance->id));

                        $trace->output("unsassigning all roles: $userid ==> $course->shortname", 1);
                    }
                }
            }
        }

        // Close db connection.
        $extdb->Close();

        $trace->output('...user enrolment synchronisation finished.');
        $trace->finished();

        return 0;
    }

    /**
     * Performs a full sync with external database.
     *
     * First it creates new courses if necessary, then
     * enrols and unenrols users.
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

        $trace->output('Starting course synchronisation...');

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return 1;
        }

        $courseconfig = get_config('moodlecourse');

        $table     = $this->get_config('newcoursetable');
        $fullname  = trim($this->get_config('newcoursefullname'));
        $shortname = trim($this->get_config('newcourseshortname'));
        $idnumber  = trim($this->get_config('newcourseidnumber'));
        $category  = trim($this->get_config('newcoursecategory'));

        $startdate = trim($this->get_config('newcoursestartdate'));
        $enddate   = trim($this->get_config('newcourseenddate'));

        // Lowercased versions - necessary because we normalise the resultset with array_change_key_case().
        $fullname_l  = strtolower($fullname);
        $shortname_l = strtolower($shortname);
        $idnumber_l  = strtolower($idnumber);
        $category_l  = strtolower($category);
        $startdatelowercased = strtolower($startdate);
        $enddatelowercased   = strtolower($enddate);

        $localcategoryfield = $this->get_config('localcategoryfield', 'id');
        $defaultcategory    = $this->get_config('defaultcategory');

        if (!$DB->record_exists('course_categories', array('id'=>$defaultcategory))) {
            $trace->output("default course category does not exist!", 1);
            $categories = $DB->get_records('course_categories', array(), 'sortorder', 'id', 0, 1);
            $first = reset($categories);
            $defaultcategory = $first->id;
        }

        $sqlfields = array($fullname, $shortname);
        if ($category) {
            $sqlfields[] = $category;
        }
        if ($idnumber) {
            $sqlfields[] = $idnumber;
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
                    $course->idnumber  = $idnumber ? $fields[$idnumber_l] : '';

                    if ($category) {
                        if (empty($fields[$category_l])) {
                            // Empty category means use default.
                            $course->category = $defaultcategory;
                        } else if ($coursecategory = $DB->get_record('course_categories', array($localcategoryfield=>$fields[$category_l]), 'id')) {
                            // Yay, correctly specified category!
                            $course->category = $coursecategory->id;
                            unset($coursecategory);
                        } else {
                            // Bad luck, better not continue because unwanted ppl might get access to course in different category.
                            $trace->output('error: invalid category '.$localcategoryfield.', can not create course: '.$fields[$shortname_l], 1);
                            continue;
                        }
                    } else {
                        $course->category = $defaultcategory;
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

            $template = false;
            if ($templatecourse) {
                if ($template = $DB->get_record('course', array('shortname'=>$templatecourse))) {
                    $template = fullclone(course_get_format($template)->get_course());
                    if (!isset($template->numsections)) {
                        $template->numsections = course_get_format($template)->get_last_section_number();
                    }
                    unset($template->id);
                    unset($template->fullname);
                    unset($template->shortname);
                    unset($template->idnumber);
                } else {
                    $trace->output("can not find template for new course!", 1);
                }
            }
            if (!$template) {
                $template = new stdClass();
                $template->summary        = '';
                $template->summaryformat  = FORMAT_HTML;
                $template->format         = $courseconfig->format;
                $template->numsections    = $courseconfig->numsections;
                $template->newsitems      = $courseconfig->newsitems;
                $template->showgrades     = $courseconfig->showgrades;
                $template->showreports    = $courseconfig->showreports;
                $template->showactivitydates = $courseconfig->showactivitydates;
                $template->maxbytes       = $courseconfig->maxbytes;
                $template->groupmode      = $courseconfig->groupmode;
                $template->groupmodeforce = $courseconfig->groupmodeforce;
                $template->visible        = $courseconfig->visible;
                $template->lang           = $courseconfig->lang;
                $template->enablecompletion = $courseconfig->enablecompletion;
                $template->startdate      = usergetmidnight(time());
                if ($courseconfig->courseenddateenabled) {
                    $template->enddate    = usergetmidnight(time()) + $courseconfig->courseduration;
                }
            }

            foreach ($createcourses as $fields) {
                $newcourse = clone($template);
                $newcourse->fullname  = $fields->fullname;
                $newcourse->shortname = $fields->shortname;
                $newcourse->idnumber  = $fields->idnumber;
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
                $c = create_course($newcourse);
                $trace->output("creating course: $c->id, $c->fullname, $c->shortname, $c->idnumber, $c->category", 1);
            }

            unset($createcourses);
            unset($template);
        }

        // Close db connection.
        $extdb->Close();

        $trace->output('...course synchronisation finished.');
        $trace->finished();

        return 0;
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

        if (
            !$this->get_config('dbtype') || !$this->get_config('newgrouptable') ||
            !$this->get_config('newgroupname') || !$this->get_config('newgroupidnumber') ||
            !$this->get_config('newgroupcourse')
        ) {
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
                    if (empty($fields[$namelow]) || empty($fields[$idnumberlow]) || empty($fields[$courselow])) {
                        $error = 'error: invalid external group record, name, id number and course are mandatory: ';
                        $trace->output($error . json_encode($fields), 1);
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
        // TODO MDL-51202: once groups can be owned by a component, update the name of the groups owned by this plugin.
        if ($description) {
            foreach (array_intersect_key($extdbgroups, $dbgroups) as $key => $group) {
                $dbgroup = $dbgroups[$key];
                $newdescription = (string) ($group[$descriptionlow] ?? '');
                if ($newdescription === '' || $newdescription === (string) $dbgroup->description) {
                    continue;
                }
                $update = new stdClass();
                $update->id = $dbgroup->id;
                $update->courseid = $dbgroup->courseid;
                $update->description = $newdescription;
                // Keep the group messaging as it is: groups_update_group() disables the group conversation if not given.
                $conversation = ['component' => 'core_group', 'itemtype' => 'groups', 'itemid' => $dbgroup->id];
                $update->enablemessaging = (int) $DB->get_field('message_conversations', 'enabled', $conversation);
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
                                   . $group[$idnumberlow] . " should belong doesn't exist, group will not be created.");
                    continue;
                }

                if ($DB->record_exists('groups', ['idnumber' => $group[$idnumberlow], 'courseid' => $courseid])) {
                    // Already exists (with a different case in the idnumber), skip.
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
                               . $newgroup->course);
                $newgroupid = groups_create_group($newgroup);

                if ($grouping && !empty($group[$groupinglow])) {
                    $groupingconditions = ['idnumber' => $group[$groupinglow], 'courseid' => $courseid];
                    $existinggrouping = $DB->get_record('groupings', $groupingconditions, 'id');
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
     * Returns the field of the remote group enrolments table that identifies the course of each row.
     *
     * It is the 'groupenrolcoursefield' setting. If it is not set, the field of the remote groups table set in the
     * 'newgroupcourse' setting is used, as both tables usually share the name of the course column.
     *
     * @return string field name, empty if none of the settings is set.
     */
    protected function get_group_enrolment_course_field(): string {
        $coursefield = trim((string) $this->get_config('groupenrolcoursefield'));
        if ($coursefield === '') {
            $coursefield = trim((string) $this->get_config('newgroupcourse'));
        }
        return $coursefield;
    }

    /**
     * Whether all the settings needed by the group enrolments synchronisation are set.
     *
     * @return bool
     */
    protected function is_group_enrolment_sync_configured(): bool {
        foreach (['dbtype', 'groupenroltable', 'groupenroluserfield', 'groupenrolgroupfield'] as $setting) {
            if (trim((string) $this->get_config($setting)) === '') {
                return false;
            }
        }
        return $this->get_group_enrolment_course_field() !== '';
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
        $userfield = trim($this->get_config('groupenroluserfield'));
        $groupcoursefield = $this->get_group_enrolment_course_field();
        $groupfield = trim($this->get_config('groupenrolgroupfield'));
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
            if (!empty($user->deleted) || !isset($user->$localuserfield) || (string) $user->$localuserfield === '') {
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
            if ($extuser === '' || $extcourse === '' || $extgroup === '') {
                continue;
            }
            $extmembers[$extcourse][$extgroup][] = $extuser;
            $extusers[$extuser] = true;
        }
        $rs->Close();

        // An empty external table (or one without any valid row) in a full synchronisation is more likely a problem of
        // the external data (e.g. the table is being reloaded) or of the settings than a request to empty all the
        // groups, so nothing is removed. The synchronisation of a single user may legitimately have no rows.
        if (!$user && !$extmembers) {
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
                if (
                    ($member->component === '' && $groupupgrading) ||
                    ($member->component === 'enrol_database' && (int) $member->itemid !== $itemid)
                ) {
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
            $membership = (object) ['id' => $member->id, 'component' => 'enrol_database', 'itemid' => $group->itemid];
            $DB->update_record('groups_members', $membership);
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
            if (!$DB->is_transaction_started() && groups_is_member($groupid, $userid)) {
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
     * @deprecated since Moodle 6.0.
     * @todo MDL-90483 Final deprecation in Moodle 7.0.
     * @param string $text
     * @return string
     */
    #[\core\attribute\deprecated(
        replacement: 'parameterised queries',
        since: '6.0',
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

        $adodb->Close();

        $this->config->debugdb = $olddebugdb;
        $CFG->debug = $olddebug;
        ini_set('display_errors', $olddisplay);
        error_reporting($CFG->debug);
        ob_end_flush();
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
