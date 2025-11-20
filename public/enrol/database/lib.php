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
        $manual_enrol_check = $this->get_config('manualenrol_cleaning');
        $manual_enrol_cleaning_mode = $this->get_config('manualenrol_cleaning_mode');

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
        $sql = $this->db_get_sql($table, array($userfield=>$user->$localuserfield), array(), false);

        if ($rs = $extdb->Execute($sql)) {
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
            $extdb->Close();
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
        $this->sync_user_group_enrolments($user);
    }

    /**
     * Forces synchronisation of user group enrolments with external database,
     * does not create new groups.
     *
     * @param stdClass $user user record
     * @return void
     */
    public function sync_user_group_enrolments($user) {
        global $CFG, $DB;

        if (!$this->get_config('dbtype') or !$this->get_config('groupenroltable')
                or !$this->get_config('userfield') or !$this->get_config('groupfield')) {
            return;
        }

        if (!$extdb = $this->db_init()) {
            return;
        }

        $table  = trim($this->get_config('groupenroltable'));
        $grouptable = trim($this->get_config('newgrouptable'));
        $idnumber = trim($this->get_config('newgroupidnumber'));
        $groupcoursefield = trim($this->get_config('newgroupcourse'));
        $userfield  = trim($this->get_config('userfield'));
        $localuserfield  = trim($this->get_config('localuserfield'));
        $localcoursefield = trim($this->get_config('localcoursefield'));
        $groupfield  = trim($this->get_config('groupfield'));

        // Warning: postgres_fdw is needed to join Moodle tables and external database tables
        $enrolsql = "SELECT row_number() over() as index, mu.id as userid, mg.id as groupid,
                            mu.$localuserfield as localuserfield, c.$localcoursefield as localcoursefield,
                            mg.idnumber as groupfield, c.id as courseid
                       FROM $table ge
                       JOIN {user} mu on (ge.$userfield = mu.$localuserfield)
                       JOIN {course} c on (ge.$groupcoursefield = c.$localcoursefield)
                       JOIN {groups} mg on (ge.$groupfield = mg.idnumber and c.id = mg.courseid)
                      WHERE mu.deleted = 0
                        AND mu.id = :userid
                        AND not exists (SELECT 1
                                          FROM {groups_members} mgm
                                         WHERE mgm.groupid = mg.id
                                           AND mgm.userid = mu.id);";

        if ($result = $DB->get_records_sql($enrolsql, array('userid' => $user->id))) {
            foreach ($result as $rs){
                $group['userid'] = $rs->userid;
                $group['groupid'] = $rs->groupid;
                $group['localuserfield'] = $rs->localuserfield;
                $group['localcoursefield'] = $rs->localcoursefield;
                $group['groupfield'] = $rs->groupfield;
                $group['courseid'] = $rs->courseid;
                $requestedgroups[$rs->index] = $group;
            }
            // Adding users to their respective groups.
            foreach ($requestedgroups as $group) {
                require_once($CFG->dirroot.'/group/lib.php');
                
                groups_add_member($group['groupid'], $group['userid'], 'enrol_database');
            }
            unset($requestedgroups);
        }

        // Upgrade group membership component if the setting is enabled
        $groupupgrading = $this->get_config('groupupgrading');
        if ($groupupgrading) {
            $updatesql = "SELECT row_number() over() as index, mu.id as userid, mg.id as groupid,
                                mu.$localuserfield as localuserfield, c.$localcoursefield as localcoursefield,
                                ge.$groupfield as groupfield, c.id as courseid
                            FROM $table ge
                            JOIN {user} mu on (ge.$userfield = mu.$localuserfield)
                            JOIN {course} c on (ge.$groupcoursefield = c.$localcoursefield)
                            JOIN {groups} mg on (ge.$groupfield = mg.idnumber and c.id = mg.courseid)
                        WHERE mu.deleted = 0
                            AND mu.id = :userid
                            AND exists (SELECT 1
                                        FROM {groups_members} mgm
                                        WHERE mgm.groupid = mg.id
                                            AND mgm.userid = mu.id
                                            AND mgm.component='');";
            if ($result = $DB->get_records_sql($updatesql, array('userid' => $user->id))) {
                foreach ($result as $rs){
                    $group['userid'] = $rs->userid;
                    $group['groupid'] = $rs->groupid;
                    $group['localuserfield'] = $rs->localuserfield;
                    $group['localcoursefield'] = $rs->localcoursefield;
                    $group['groupfield'] = $rs->groupfield;
                    $group['courseid'] = $rs->courseid;
                    $requestedgroups[$rs->index] = $group;
                }
                // Removing users without component from groups and adding them back.
                foreach ($requestedgroups as $group) {
                    require_once($CFG->dirroot.'/group/lib.php');

                    groups_remove_member($group['groupid'], $group['userid']);
                    groups_add_member($group['groupid'], $group['userid'], 'enrol_database');
                }
                unset($requestedgroups);
            }
        }

        $unenrolsql = "SELECT row_number() over() as index, mgm.groupid, mgm.userid,
                              mu.$localuserfield as localuserfield, mg.idnumber as groupfield, mg.courseid as courseid
                         FROM {groups_members} mgm
                         JOIN {user} mu on (mu.id = mgm.userid)
                         JOIN {groups} mg on (mgm.groupid = mg.id)
                         JOIN $grouptable g on (mg.idnumber = g.$idnumber)
                        WHERE mu.deleted = 0
                          AND mu.id = :userid
                          AND mgm.component = 'enrol_database'
                          AND (mu.$localuserfield,mgm.groupid) not in ( SELECT ge2.$userfield, mgm2.groupid
                                                                          FROM $table ge2
                                                                          JOIN {user} u2 on (ge2.$userfield = u2.$localuserfield)
                                                                          JOIN {groups} mg2 on (ge2.$groupfield = mg2.idnumber)
                                                                          JOIN {groups_members} mgm2 on (mg2.id = mgm2.groupid AND u2.id=mgm2.userid));";
        if ($result = $DB->get_records_sql($unenrolsql, array('userid' => $user->id))) {
            foreach ($result as $rs){
                $group['userid'] = $rs->userid;
                $group['groupid'] = $rs->groupid;
                $group['localuserfield'] = $rs->localuserfield;
                $group['groupfield'] = $rs->groupfield;
                $group['courseid'] = $rs->courseid;
                $requestedunenrolments[$rs->index] = $group;
            }
            // Deletes the link between the specified user and group.
            foreach ($requestedunenrolments as $group) {
                require_once($CFG->dirroot.'/group/lib.php');
                groups_remove_member($group['groupid'], $group['userid']);
            }
            unset($requestedunenrolments);
        }

        // Close db connection.
        $extdb->Close();
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
        
        $manual_enrol_check = $this->get_config('manualenrol_cleaning');
        $manual_enrol_cleaning_mode = $this->get_config('manualenrol_cleaning_mode');

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
            $sql = $this->db_get_sql($table, array(), array($coursefield), true);
            if ($rs = $extdb->Execute($sql)) {
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
            $sql = $this->db_get_sql($table, array($coursefield=>$course->mapping), $sqlfields);
            if ($rs = $extdb->Execute($sql)) {
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

                        // If new mode is enabled, check if there is a manual enrolment for the recently enrolled user-course and remove the duplicated manual enrolment
                        if ($manual_enrol_check and $manual_enrol_cleaning_mode == 'new') {
                            // If there are more roles assigned to the user in that course, in order to remove the manual enrolment those roles and their archetypes must have higher sortorders
                            $sql_check_manual_enrol = "SELECT e.id AS enrolid, ctx.id as contextid, ctx.instanceid as instanceid
                                                         FROM {enrol} e
                                                         JOIN {user_enrolments} ue ON (e.id = ue.enrolid)
                                                         JOIN {role_assignments} ra ON (ue.userid=ra.userid AND (ue.enrolid=ra.itemid OR (e.enrol='manual' AND ra.itemid=0)))
                                                         JOIN {context} ctx ON (ra.contextid=ctx.id AND ctx.contextlevel=50)
                                                         JOIN {course} c ON (ctx.instanceid=c.id AND e.courseid = c.id)
                                                         JOIN {role} r ON (ra.roleid=r.id)
                                                        WHERE e.enrol = 'manual'
                                                          AND ra.itemid=0
                                                          AND ue.userid = :userid
                                                          AND e.courseid = :courseid
                                                          AND NOT EXISTS (SELECT r2.id
                                                                            FROM {role_assignments} ra2
                                                                            JOIN {role} r2 ON (ra2.roleid=r2.id)
                                                                            JOIN {role} r3 ON (r2.archetype = r3.shortname)
                                                                            JOIN {context} ctx2 ON (ra2.contextid=ctx2.id AND ctx2.contextlevel=50)
                                                                            JOIN {course} c2 ON (ctx2.instanceid=c2.id ),
                                                                                 {role} externalrole
                                                                           WHERE ra2.userid=ra.userid
                                                                             AND c2.id = e.courseid
                                                                             AND externalrole.id= :roleid
                                                                             AND (r2.sortorder < externalrole.sortorder OR r3.sortorder < externalrole.sortorder) 
                                                                         )
                                                    ";
                            $params_check_manual = array('userid' => $userid, 'courseid' => $course->id, 'roleid' => $roleid);
                            $manual_enrol = $DB->get_record_sql($sql_check_manual_enrol, $params_check_manual);
                            if ($manual_enrol) {
                                $enrol_plugin = enrol_get_plugin('manual');
                                $manual_instance = $DB->get_record('enrol', array('id' => $manual_enrol->enrolid));
                        
                                if ($manual_instance) {
                                    role_unassign_all(array('contextid'=>$manual_enrol->contextid, 'userid'=>$userid, 'component'=>'', 'itemid'=>0));
                                    $trace->output("manually assigned roles removed for $localuserfield '".$useridentifier[$userid]."' in course $course->mapping.",1);
                                    $enrol_plugin->unenrol_user($manual_instance, $userid);
                                    $trace->output("manual enrolment removed for $localuserfield '".$useridentifier[$userid]."' in course $course->mapping.",1);
                                } else {
                                    $trace->output("error: Could not find enrol instance for manual enrolment in course $course->mapping.",1);
                                }
                            }
                        } 
                    }
                    // If full mode is enabled, check if there is a manual enrolment for every user in the external db and remove the duplicated manual enrolment
                    if ($manual_enrol_check and $manual_enrol_cleaning_mode == 'full') {
                        // If there are more roles assigned to the user in that course, in order to remove the manual enrolment those roles and their archetypes must have higher sortorders
                        $sql_check_manual_enrol = "SELECT e.id AS enrolid, ctx.id as contextid, ctx.instanceid as instanceid
                                                     FROM {enrol} e
                                                     JOIN {user_enrolments} ue ON (e.id = ue.enrolid)
                                                     JOIN {role_assignments} ra ON (ue.userid=ra.userid AND (ue.enrolid=ra.itemid OR (e.enrol='manual' AND ra.itemid=0)))
                                                     JOIN {context} ctx ON (ra.contextid=ctx.id AND ctx.contextlevel=50)
                                                     JOIN {course} c ON (ctx.instanceid=c.id AND e.courseid = c.id)
                                                     JOIN {role} r ON (ra.roleid=r.id)
                                                    WHERE e.enrol = 'manual'
                                                      AND ra.itemid=0
                                                      AND ue.userid = :userid
                                                      AND e.courseid = :courseid
                                                      AND NOT EXISTS (SELECT r2.id
                                                                        FROM {role_assignments} ra2
                                                                        JOIN {role} r2 ON (ra2.roleid=r2.id)
                                                                        JOIN {role} r3 ON (r2.archetype = r3.shortname)
                                                                        JOIN {context} ctx2 ON (ra2.contextid=ctx2.id AND ctx2.contextlevel=50)
                                                                        JOIN {course} c2 ON (ctx2.instanceid=c2.id ),
                                                                             {role} externalrole
                                                                       WHERE ra2.userid=ra.userid
                                                                         AND c2.id = e.courseid
                                                                         AND externalrole.id= :roleid
                                                                         AND (r2.sortorder < externalrole.sortorder OR r3.sortorder < externalrole.sortorder) 
                                                                        )
                                                    ";
                        $params_check_manual = array('userid' => $userid, 'courseid' => $course->id, 'roleid' => $roleid);
                        $manual_enrol = $DB->get_record_sql($sql_check_manual_enrol, $params_check_manual);
                        if ($manual_enrol) {
                            $enrol_plugin = enrol_get_plugin('manual');
                            $manual_instance = $DB->get_record('enrol', array('id' => $manual_enrol->enrolid));
                    
                            if ($manual_instance) {
                                role_unassign_all(array('contextid'=>$manual_enrol->contextid, 'userid'=>$userid, 'component'=>'', 'itemid'=>0));
                                $trace->output("manually assigned roles removed for $localuserfield '".$useridentifier[$userid]."' in course $course->mapping.",1);
                                $enrol_plugin->unenrol_user($manual_instance, $userid);
                                $trace->output("manual enrolment removed for $localuserfield '".$useridentifier[$userid]."' in course $course->mapping.",1);
                            } else {
                                $trace->output("error: Could not find enrol instance for manual enrolment in course $course->mapping.",1);
                            }
                        }
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
        $summary =   trim($this->get_config('newcoursesummary'));
        $template =  trim($this->get_config('newcoursetemplate') ?? '');
        $category  = trim($this->get_config('newcoursecategory'));

        $startdate = trim($this->get_config('newcoursestartdate'));
        $enddate   = trim($this->get_config('newcourseenddate'));

        // Lowercased versions - necessary because we normalise the resultset with array_change_key_case().
        $fullname_l  = strtolower($fullname);
        $shortname_l = strtolower($shortname);
        $idnumber_l  = strtolower($idnumber);
        $summary_l   = strtolower($summary);
        $template_l  = strtolower($template);
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
        if ($summary) {
            $sqlfields[] = $summary_l;
        if ($template) {
            $sqlfields[] = $template_l;
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

        $sql = $this->db_get_sql($table, array(), $sqlfields, true);
        $createcourses = array();
        if ($rs = $extdb->Execute($sql)) {
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
                    $course->summary = $summary_l ? $fields[$summary_l] : '';
                    $course->template = $template_l ? trim($fields[$template_l]) : '';

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

            $defaulttemplate = false;
            $defaulttemplateid = 0;
            if ($templatecourse) {
                // We don't set $defaulttemplateid here (and keep it as 0), because we can't import
                // content from this template (as it's not really a course!).
                // The next line has been removed on Moodle 4.5 core
                //$courseconfig = get_config('moodlecourse');
                if ($defaulttemplate = $DB->get_record('course', array('shortname'=>$templatecourse))) {
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
                } else {
                    $trace->output("can not find template for new course!", 1);
                }
            }
            if (!$defaulttemplate) {
                $defaulttemplate = new stdClass();
                $defaulttemplate->summary        = '';
                $defaulttemplate->summaryformat  = FORMAT_HTML;
                $defaulttemplate->format         = $courseconfig->format;
                $defaulttemplate->numsections    = $courseconfig->numsections;
                $defaulttemplate->newsitems      = $courseconfig->newsitems;
                $defaulttemplate->showgrades     = $courseconfig->showgrades;
                $defaulttemplate->showreports    = $courseconfig->showreports;
                $defaulttemplate->maxbytes       = $courseconfig->maxbytes;
                $defaulttemplate->groupmode      = $courseconfig->groupmode;
                $defaulttemplate->groupmodeforce = $courseconfig->groupmodeforce;
                $defaulttemplate->visible        = $courseconfig->visible;
                $defaulttemplate->lang           = $courseconfig->lang;
                $defaulttemplate->enablecompletion = $courseconfig->enablecompletion;
                $defaulttemplate->groupmodeforce = $courseconfig->groupmodeforce;
                $defaulttemplate->startdate      = usergetmidnight(time());
                if ($courseconfig->courseenddateenabled) {
                    $defaulttemplate->enddate    = usergetmidnight(time()) + $courseconfig->courseduration;
                }
            }

            foreach ($createcourses as $fields) {
                $templateid = $defaulttemplateid;
                if ($fields->template) {
                    if ($coursetemplate = $DB->get_record('course',
                            array($this->get_config('localtemplatefield') => $fields->template))) {
                        $newcourse = fullclone(course_get_format($coursetemplate)->get_course());
                        $templateid = $newcourse->id;
                        unset($newcourse->id);
                        unset($newcourse->fullname);
                        unset($newcourse->shortname);
                        unset($newcourse->idnumber);
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
                               "$newcourse->category", 1);

                if ($templateid) {
                    // If we have a real template (i.e., based on an existing course) duplicate it (including all activities,
                    // blocks, filters and enrolments) and update the resulting course with the new course fields.
                    require_once("$CFG->dirroot/course/externallib.php");
                    require_once("$CFG->dirroot/group/lib.php");

                    // This requires special permissions. Temporarily elevate our privileges.
                    // And remember to drop the elevated privileges as soon as they are not needed.
                    global $USER;
                    $olduser = $USER;
                    $USER = get_admin();

                    static $backupsettings = array(
                        array('name' => 'users', 'value' => 0)
                    );

                    $duplicatecoursereturns = core_course_external::duplicate_course($templateid, $newcourse->fullname,
                            $newcourse->shortname, $newcourse->category, 1, $backupsettings);

                    $updatecourse = array('courses' => array(
                            'id' => $duplicatecoursereturns['id'],
                            'idnumber' => $newcourse->idnumber,
                            'summary' => $newcourse->summary));
                    core_course_external::update_courses($updatecourse);
                    groups_delete_groups($duplicatecoursereturns['id'], $showfeedback=false);
                    groups_delete_groupings($duplicatecoursereturns['id'], $showfeedback=false);

                    $USER = $olduser;
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
        $description = trim($this->get_config('newgroupdesc'));
        $course = trim($this->get_config('newgroupcourse'));
        $grouping = trim($this->get_config('newgroupgroupings'));
        $groupingcreation = $this->get_config('groupingcreation');
        $messaging = $this->get_config('groupmessaging');

        $namelow = strtolower($name);
        $idnumberlow = strtolower($idnumber);
        $courselow = strtolower($course);
        $descriptionlow = strtolower($description);
        $groupinglow = strtolower($grouping);
        
        $extdbgroups = array();
        $dbgroups = array();

        // External db groups.
        $sqlfields = array($namelow, $idnumberlow, $courselow);
        if ($description) {
            $sqlfields[] = $descriptionlow;
        }
        if ($grouping) {
            $sqlfields[] = $groupinglow;
        }
        $sql = $this->db_get_sql($table, array(), $sqlfields, true);
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = $this->db_decode($fields);
                    if (empty($fields[$namelow]) or empty($fields[$idnumberlow]) or empty($fields[$courselow])) {
                        $trace->output('error: invalid external group record, name, id number and course shortname are mandatory:
                         '.json_encode($fields), 1);
                        continue;
                    }
                    $extdbgroups[$fields[$courselow].$fields[$idnumberlow]] = $fields;
                }
            }
        } else {
            $extdb->Close();
            $trace->output('Error reading data from the external groups table');
            $trace->finished();
            return 4;
        }

        // Moodle db groups (existing groups).
        $dbsql = "SELECT c.shortname||g.idnumber as index, g.idnumber, g.name, g.description, c.shortname as coursename
                    FROM {groups} g 
                         JOIN {course} c on (g.courseid=c.id)
                   WHERE g.idnumber is not null 
                     AND g.idnumber<>''";
        if ($result = $DB->get_records_sql($dbsql)) {
            foreach ($result as $rs) {
                $group[$idnumberlow] = $rs->idnumber;
                $group[$namelow] = $rs->name;
                $group[$descriptionlow] = $rs->description;
                $group[$courselow] = $rs->coursename;
                $dbgroups[$rs->index] = $group;
            }
        }

        error_reporting(E_ALL & ~E_NOTICE); // Avoids showing the php notice thrown by the next function:
        $creategroups = array_udiff($extdbgroups, $dbgroups, function($a, $b) {
            return strcmp(serialize($a), serialize($b));
        });
        error_reporting($CFG->debug); // Set again the debug messaging level configured in the site administration.

        if ($creategroups) {
            require_once("$CFG->dirroot/group/lib.php");
            foreach ($creategroups as $group) {
                // The course to which the group will belong must exist.
                if (!$DB->record_exists('course', array('shortname' => $group[$courselow]))) {
                    $trace->output("  [x] Course shortname " . $group[$courselow] . " to which the group ID number "
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
                $newgroup->description = $group[$descriptionlow];
                $newgroup->course = $group[$courselow];
                $newgroup->enablemessaging = $messaging;

                $trace->output("  creating group: $newgroup->name, $newgroup->idnumber, $newgroup->description, in course "
                .$newgroup->course);
                $newgroupid = groups_create_group($newgroup);

                if ($group[$groupinglow]){
                    $recordset = $DB->get_record('groupings', array('idnumber' => $group[$groupinglow]));
                    if(!$recordset and $groupingcreation) {
                        $newgrouping = new stdClass();
                        $newgrouping->idnumber = $group[$groupinglow];
                        $newgrouping->courseid = $courseid;
                        $newgrouping->name = $group[$groupinglow];
                        $newgroupingid = groups_create_grouping($newgrouping);

                        groups_assign_grouping($newgroupingid, $newgroupid);
                    } else {
                        groups_assign_grouping($recordset->id, $newgroupid);
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
     * Forces synchronisation of group enrolments with external database,
     *
     * @param progress_trace $trace
     * @return void
     */
    public function sync_group_enrolments(progress_trace $trace) {
        global $CFG, $DB;

        if (!$this->get_config('dbtype') or !$this->get_config('groupenroltable')
                or !$this->get_config('userfield') or !$this->get_config('groupfield')) {
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

        $table  = trim($this->get_config('groupenroltable'));
        $grouptable = trim($this->get_config('newgrouptable'));
        $idnumber = trim($this->get_config('newgroupidnumber'));
        $groupcoursefield = trim($this->get_config('newgroupcourse'));
        $userfield  = trim($this->get_config('userfield'));
        $localuserfield  = trim($this->get_config('localuserfield'));
        $localcoursefield = trim($this->get_config('localcoursefield'));
        $groupfield  = trim($this->get_config('groupfield'));

        // Warning: postgres_fdw is needed to join Moodle tables and external database tables
        $enrolsql = "SELECT row_number() over() as index, mu.id as userid, mg.id as groupid,
                            mu.$localuserfield as localuserfield, c.$localcoursefield as localcoursefield,
                            mg.idnumber as groupfield, c.id as courseid
                       FROM $table ge
                       JOIN {user} mu on (ge.$userfield = mu.$localuserfield)
                       JOIN {course} c on (ge.$groupcoursefield = c.$localcoursefield)
                       JOIN {groups} mg on (ge.$groupfield = mg.idnumber and c.id = mg.courseid)
                      WHERE mu.deleted = 0
                        AND not exists (SELECT 1
                                          FROM {groups_members} mgm
                                         WHERE mgm.groupid = mg.id
                                           AND mgm.userid = mu.id);";
        if ($result = $DB->get_records_sql($enrolsql)) {
            foreach ($result as $rs){
                $group['userid'] = $rs->userid;
                $group['groupid'] = $rs->groupid;
                $group['localuserfield'] = $rs->localuserfield;
                $group['localcoursefield'] = $rs->localcoursefield;
                $group['groupfield'] = $rs->groupfield;
                $group['courseid'] = $rs->courseid;
                $requestedgroups[$rs->index] = $group;
            }
            // Adding users to their respective groups.
            foreach ($requestedgroups as $group) {
                require_once($CFG->dirroot.'/group/lib.php');

                if(groups_add_member($group['groupid'], $group['userid'], 'enrol_database')){
                    $trace->output("  adding user ".$group['localuserfield']." to group: ".$group['groupfield'].
                                   ": ".$CFG->wwwroot."/group/overview.php?id=".$group['courseid']."&group=".$group['groupid']);
                } else {
                    $errorcourse = $DB->get_record("groups",array("id"=>$group['groupid']),"courseid");
                    $trace->output("  error adding user ".$group['localuserfield']." to group ".$group['groupfield'].", not enrolled in course ID ".$errorcourse->courseid.
                                   ": ".$CFG->wwwroot."/group/overview.php?id=".$group['courseid']."&group=".$group['groupid']);
                }
            }
            unset($requestedgroups);
        }

        // Upgrade group membership component if the setting is enabled
        $groupupgrading = $this->get_config('groupupgrading');
        if ($groupupgrading) {
            $updatesql = "SELECT row_number() over() as index, mu.id as userid, mg.id as groupid,
                                mu.$localuserfield as localuserfield, c.$localcoursefield as localcoursefield,
                                ge.$groupfield as groupfield, c.id as courseid
                            FROM $table ge
                            JOIN {user} mu on (ge.$userfield = mu.$localuserfield)
                            JOIN {course} c on (ge.$groupcoursefield = c.$localcoursefield)
                            JOIN {groups} mg on (ge.$groupfield = mg.idnumber and c.id = mg.courseid)
                        WHERE mu.deleted = 0
                            AND exists (SELECT 1
                                        FROM {groups_members} mgm
                                        WHERE mgm.groupid = mg.id
                                            AND mgm.userid = mu.id
                                            AND mgm.component='');";
            if ($result = $DB->get_records_sql($updatesql)) {
                foreach ($result as $rs){
                    $group['userid'] = $rs->userid;
                    $group['groupid'] = $rs->groupid;
                    $group['localuserfield'] = $rs->localuserfield;
                    $group['localcoursefield'] = $rs->localcoursefield;
                    $group['groupfield'] = $rs->groupfield;
                    $group['courseid'] = $rs->courseid;
                    $requestedgroups[$rs->index] = $group;
                }
                // Removing users without component from groups and adding them back.
                foreach ($requestedgroups as $group) {
                    require_once($CFG->dirroot.'/group/lib.php');

                    $trace->output("  removing user ".$group['localuserfield']." with empty component from group ".$group['groupfield'].
                                ": ".$CFG->wwwroot."/group/overview.php?id=".$group['courseid']."&group=".$group['groupid']);
                    groups_remove_member($group['groupid'], $group['userid']);
                    if(groups_add_member($group['groupid'], $group['userid'], 'enrol_database')){
                        $trace->output("  adding back the user ".$group['localuserfield']." to group: ".$group['groupfield'].
                                    ": ".$CFG->wwwroot."/group/overview.php?id=".$group['courseid']."&group=".$group['groupid']);
                    } else {
                        $errorcourse = $DB->get_record("groups",array("id"=>$group['groupid']),"courseid");
                        $trace->output("  error adding user ".$group['localuserfield']." to group ".$group['groupfield'].", not enrolled in course ".$errorcourse->courseid.
                                    ": ".$CFG->wwwroot."/group/overview.php?id=".$group['courseid']."&group=".$group['groupid']);
                    }
                }
                unset($requestedgroups);
            }
        }

        $unenrolsql = "SELECT row_number() over() as index, mgm.groupid, mgm.userid,
                              mu.$localuserfield as localuserfield, mg.idnumber as groupfield, mg.courseid as courseid
                         FROM {groups_members} mgm
                         JOIN {user} mu on (mu.id = mgm.userid)
                         JOIN {groups} mg on (mgm.groupid = mg.id)
                         JOIN $grouptable g on (mg.idnumber = g.$idnumber)
                        WHERE mu.deleted = 0
                          AND mgm.component = 'enrol_database'
                          AND (mu.$localuserfield,mgm.groupid) not in ( SELECT ge2.$userfield, mgm2.groupid
                                                                          FROM $table ge2
                                                                          JOIN {user} u2 on (ge2.$userfield = u2.$localuserfield)
                                                                          JOIN {groups} mg2 on (ge2.$groupfield = mg2.idnumber)
                                                                          JOIN {groups_members} mgm2 on (mg2.id = mgm2.groupid AND u2.id=mgm2.userid));";
        if ($result = $DB->get_records_sql($unenrolsql)) {
            foreach ($result as $rs){
                $group['userid'] = $rs->userid;
                $group['groupid'] = $rs->groupid;
                $group['localuserfield'] = $rs->localuserfield;
                $group['groupfield'] = $rs->groupfield;
                $group['courseid'] = $rs->courseid;
                $requestedunenrolments[$rs->index] = $group;
            }
            // Deletes the link between the specified user and group.
            foreach ($requestedunenrolments as $group) {
                require_once($CFG->dirroot.'/group/lib.php');
                $trace->output("  removing user ".$group['localuserfield']." from group ".$group['groupfield'].
                               ": ".$CFG->wwwroot."/group/overview.php?id=".$group['courseid']."&group=".$group['groupid']);
                groups_remove_member($group['groupid'], $group['userid']);
            }
            unset($requestedunenrolments);
        }

        // Close db connection.
        $extdb->Close();

        $trace->output("...group enrolment synchronisation finished.\n");
        $trace->finished();
        return 0;
    }

    protected function db_get_sql($table, array $conditions, array $fields, $distinct = false, $sort = "") {
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

    protected function db_addslashes($text) {
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
 * Prevent removal of enrol_database group member allocation.
 * @param int $itemid
 * @param int $groupid
 * @param int $userid
 * @return bool
 */
function enrol_database_allow_group_member_remove($itemid, $groupid, $userid) {
    return false;
}