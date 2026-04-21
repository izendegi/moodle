<?php

/**
 * Contains class with utils to share
 *
 * @package    paygw_mucommerce
 * @copyright  2023 Onenpro <info@onenpro.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_mucommerce;

use core\context;

class mucommerce_enrolment_checker {
    
    public static function is_enrolled(context $context, $user = null, $withcapability = '', $onlyactive = false) {
        global $USER, $DB;

        // First find the course context.
        $coursecontext = $context->get_course_context();
    
        // Make sure there is a real user specified.
        if ($user === null) {
            $userid = isset($USER->id) ? $USER->id : 0;
        } else {
            $userid = is_object($user) ? $user->id : $user;
        }
    
        if (empty($userid)) {
            // Not-logged-in!
            return false;
        } else if (isguestuser($userid)) {
            // Guest account can not be enrolled anywhere.
            return false;
        }
    
        // Try cached info first - the enrolled flag is set only when active enrolment present.
        if ($USER->id == $userid) {
            $coursecontext->reload_if_dirty();
            if (isset($USER->enrol['enrolled'][$coursecontext->instanceid])) {
                if ($USER->enrol['enrolled'][$coursecontext->instanceid] > time()) {
                    if ($withcapability and !has_capability($withcapability, $context, $userid)) {
                        return false;
                    }
                    return true;
                }
            }
        }

        if ($onlyactive) {
            // Look for active enrolments only.
            $until = enrol_get_enrolment_end($coursecontext->instanceid, $userid);

            if ($until === false) {
                return false;
            }

            if ($USER->id == $userid) {
                if ($until == 0) {
                    $until = ENROL_MAX_TIMESTAMP;
                }
                $USER->enrol['enrolled'][$coursecontext->instanceid] = $until;
                if (isset($USER->enrol['tempguest'][$coursecontext->instanceid])) {
                    unset($USER->enrol['tempguest'][$coursecontext->instanceid]);
                    remove_temp_course_roles($coursecontext);
                }
            }

        } else {
            // Any enrolment is good for us here, even outdated, disabled or inactive.
            $sql = "SELECT 'x'
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
                      JOIN {user} u ON u.id = ue.userid
                     WHERE ue.userid = :userid AND u.deleted = 0";
            $params = array('userid' => $userid, 'courseid' => $coursecontext->instanceid);
            if (!$DB->record_exists_sql($sql, $params)) {
                return false;
            }
        }
    
        if ($withcapability and !has_capability($withcapability, $context, $userid)) {
            return false;
        }
    
        return true;
    }
}