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
 * Library File.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_user\fields;

/**
 * Isset Entity setting setted
 * @return  boolean validation
 */
function block_smowl_check_entity() {
    $entity = get_config('block_smowl', 'entity');
    $password = get_config('block_smowl', 'password');
    $apikey = get_config('block_smowl', 'apikey');
    $jwtsecret = get_config('block_smowl', 'smowljwtsecret');
    if (strcasecmp($entity, '') == 0 ||
        strcasecmp($password, '') == 0 ||
        strcasecmp($apikey, '') == 0 ||
        strcasecmp($jwtsecret, '') == 0){
        return false;
    }
    return true;
}

/**
 * If not isset Entity setting setted return to course with error.
 * @see $DB
 * @see block_smowl_check_entity()
 * @param  object $course
 * @return void or redirect
 */
function block_smowl_check_entity_navigation($course = SITEID) {
    global $DB;
    if (!block_smowl_check_entity()) {
        if (!is_object($course)) {
            $course = $DB->get_record('course', ['id' => $course]);
        }
        $message = get_string('noticeemptyentitynavigation', 'block_smowl');
        redirect(course_get_url($course), $message, null, \core\output\notification::NOTIFY_ERROR);
    }
}

/**
 * If we are in the site block with siteadmin capabilities
 * @see $OUTPUT
 * @see moodle_url()
 * @see html_writer::link()
 * @return content
 */
function block_smowl_admin_site_content() {
    global $OUTPUT;
    $items = [];
    $icons = [];
    $params = [];

    // Bulk Activation.
    $text = get_string('bulkactive', 'block_smowl');
    $params['action'] = 'bulkactive';
    $url = new moodle_url('/blocks/smowl/bulkmanage.php', $params);
    $items[] = html_writer::link($url, $text);
    // Bulk Groups.
    $text = get_string('bulkgroups', 'block_smowl');
    $params['action'] = 'bulkgroups';
    $url = new moodle_url('/blocks/smowl/bulkmanage.php', $params);
    $items[] = html_writer::link($url, $text);

    $list = $OUTPUT->list_block_contents($icons, $items);
    return $list;
}

/**
 * Isset block in this course
 * @see $DB
 * @see context_course::instance()
 * @param  int $courseid
 * @return object block_instance
 */
function block_smowl_check_course_block($courseid = SITEID) {
    global $DB;
    $params = [
        'blockname' => 'smowl',
        'courseid' => $courseid,
        'contextlevel' => CONTEXT_COURSE,
    ];
    // Course select.
    $sql = "SELECT bi.*
            FROM {block_instances} bi
            JOIN {context} c ON bi.parentcontextid = c.id AND c.contextlevel = :contextlevel
            WHERE bi.blockname = :blockname AND c.instanceid = :courseid
            ORDER BY bi.id";

    $sqlreturn = $DB->get_records_sql($sql, $params);
    if (empty($sqlreturn)) {
        return new stdClass();
    }
    return array_shift($sqlreturn);
}

/**
 * Check instances with 'tracking'
 * Track no quiz instances to set if is 0.
 * @see $DB
 * @see block_smowl_get_instances()
 * @see block_smowl_delete_instance()
 * @see block_smowl_post_instances()
 * @see block_smowl_settings_check_changes()
 * @see block_smowl_api_send_lti_update_deployment()
 * @return void
 */
function block_smowl_check_modules_track() {
    global $DB;
    // Update to LTI.
    block_smowl_api_send_lti_update_deployment();

    // Check tracking if 0 -> only Quiz.
    if (get_config('block_smowl', 'tracking')) {
        return;
    }
    // Select SMOWL block_instance if exists.
    $blockinstances = block_smowl_get_instances();
    if (empty($blockinstances)) {
        block_smowl_settings_check_changes();
        return;
    }

    $deleted = [];
    foreach ($blockinstances as $instance) {
        if ($instance->name !== "quiz") {
            block_smowl_delete_instance($instance);
            $deleted[$instance->blockinstance] = $instance;
        }
    }
    // Deleted post.
    block_smowl_post_instances([], $deleted);
    block_smowl_settings_check_changes();
}

/**
 * Get all blocks instances
 *
 * @see $DB
 * @see context_module::instance()
 * @param  int $courseid
 * @param  array $created
 * @return array of objects
 */
function block_smowl_get_instances($courseid = 0, $created = []) {
    global $DB;
    $params = [
        'blockname' => 'smowl',
        'contextlevel' => CONTEXT_MODULE,
    ];
    list($notinsql, $notinparams) = $DB->get_in_or_equal(
        ['mod-quiz-attempt', 'mod-quiz-summary'],
        SQL_PARAMS_NAMED,
        'nopatt',
        false
    );
    $params = array_merge($params, $notinparams);
    $in = '';
    if ($courseid) {
        $params['courseid'] = $courseid;
        $in = 'AND cm.course = :courseid';
    } else if (!empty($created)) {
        $in = 'AND cm.id IN ('.implode(',', $created).')';
    }

    $sql = "SELECT cm.id ,bi.id AS blockinstance, c.id AS context, cm.id AS coursemodule,
                   cm.instance AS moduleinstance, cm.course, m.name
              FROM {block_instances} bi
              JOIN {context} c ON bi.parentcontextid = c.id AND c.contextlevel = :contextlevel
              JOIN {course_modules} cm ON c.instanceid = cm.id $in
              JOIN {modules} m ON m.id = cm.module
             WHERE bi.blockname = :blockname AND bi.pagetypepattern $notinsql
          ORDER BY cm.course ASC";
    return $DB->get_records_sql($sql, $params);
}

/**
 * Check permissions bulkactions
 *
 * @see $USER
 * @see block_smowl_user_in_alternative_role()
 * @see has_capability()
 * @param context $context
 * @return boolean
 */
function block_smowl_user_can_managebulkactions($context) {
    global $USER;
    // Add moodle/site:config capability for siteadmin.
    $siteadmin = has_capability('moodle/site:config', context_system::instance());
    $alternativerole = block_smowl_user_in_alternative_role($context);

    if ($siteadmin && !$alternativerole) {
        return true;
    }
    return false;
}

/**
 * Check if user is in alternative role - like navigationlib:in_alternative_role
 *
 * @see $PAGE
 * @see $USER
 * @return int id de role or false.
 */
function block_smowl_user_in_alternative_role($context) {
    global $USER, $PAGE;

    if (!empty($USER->access['rsw']) && is_array($USER->access['rsw'])) {
        if (!empty($PAGE->context->path) && !empty($USER->access['rsw'][$PAGE->context->path])) {
            return $USER->access['rsw'][$PAGE->context->path];
        }
        foreach ($USER->access['rsw'] as $key => $role) {
            if (strpos($context->path, $key) === 0) {
                return $role;
            }
        }
    }
    return false;
}

/**
 * Check if will create or delete instances
 * @see $DB
 * @see context_module::instance()
 * @see block_smowl_check_course_block()
 * @see block_smowl_create_course_instance()
 * @see block_smowl_create_instance()
 * @see block_smowl_accessrule_smowlcheckcam_is_active()
 * @see block_smowl_accessrule_smowlcheckcam_active_quiz()
 * @see block_smowl_delete_instance()
 * @see block_smowl_accessrule_smowlcheckcam_disable_quiz()
 * @see block_smowl_post_instances()
 * @see block_smowl_update_quiz_blocks()
 * @param array $instances instances selecteds
 * @param array $blockinstances instances actives
 * @param boolean $checkcourseblock check if in courseblock
 * @param boolean $sendtosmowl true if we need to send information to SMOWL
 * @return void
 */
function block_smowl_check_instances($instances, $blockinstances, $checkcourseblock = false, $sendtosmowl = true) {
    global $DB;
    $created = [];
    $deleted = [];
    $update = [];
    $courses = [];
    // Manage instances.
    foreach ($instances as $instancepatt => $check) {
        $index = strpos($instancepatt, "-", 4);
        $pagetypepattern = substr($instancepatt, 0, $index + 1);
        $instanceid = substr($instancepatt, $index + 1);
        $courseid = 0;
        if ($checkcourseblock) {
            $index2 = strpos($instancepatt, "-", $index + 1);
            $instanceid = substr($instancepatt, $index + 1, $index2 - $index - 1);
            $courseid = substr($instancepatt, $index2 + 1);
        }
        // Create or delete.
        if ($check) {
            if (!isset($blockinstances[$instanceid]) || empty($blockinstances[$instanceid])) {
                $params = ['instanceid' => $instanceid];
                $params['contextlevel'] = CONTEXT_MODULE;
                $modcontext = $DB->get_record('context', $params);
                if ($checkcourseblock && $courseid != 0 && !in_array($courseid, $courses)) {
                    $cbi = block_smowl_check_course_block($courseid);
                    if (isset($cbi->id)) {
                        $courses[] = $courseid;
                    } else {
                        $bi = block_smowl_create_course_instance($courseid);
                        $courses[] = $courseid;
                    }
                }
                $bi = block_smowl_create_instance($modcontext->id, $pagetypepattern);
                // Start smowlcheckcam.
                // In case it is a quiz and we have the smowlcheckcam accessrule active.
                $smowlcheckcamactive = block_smowl_accessrule_smowlcheckcam_is_active();
                if ($pagetypepattern == "mod-quiz-" && $smowlcheckcamactive) {
                    block_smowl_accessrule_smowlcheckcam_active_quiz($instanceid);
                }
                // End smowlcheckcam.
                $update[] = $instanceid;
                // Create instance event.
                $event = \block_smowl\event\instance_created::create_instance($modcontext->id, $bi);
                $event->trigger();
            }
            $created[] = $instanceid;
        } else {
            if (isset($blockinstances[$instanceid])) {
                block_smowl_delete_instance($blockinstances[$instanceid]);
                // Start smowlcheckcam.
                // In case it is a quiz and we have the smowlcheckcam accessrule active.
                $smowlcheckcamactive = block_smowl_accessrule_smowlcheckcam_is_active();
                if ($pagetypepattern == "mod-quiz-" && $smowlcheckcamactive) {
                    block_smowl_accessrule_smowlcheckcam_disable_quiz($instanceid);
                }
                // End smowlcheckcam.
                $deleted[$instanceid] = $blockinstances[$instanceid];
                // Delete instance event.
            }
        }
    }
    // Update quiz view blocks.
    block_smowl_update_quiz_blocks($update);
    // Post with createds and deleteds.
    if ($sendtosmowl) {
        block_smowl_post_instances($created, $deleted);
    }
}

/**
 * Check if will create or delete instances for diferents courses
 * @see $DB
 * @see context_module::instance()
 * @see block_smowl_check_course_block()
 * @see block_smowl_create_course_instance()
 * @see block_smowl_create_instance()
 * @see block_smowl_delete_instance()
 * @see block_smowl_update_quiz_blocks()
 * @see block_smowl_post_instances()
 * @param array $instances instances selecteds
 * @param array $blockinstances instances actives
 * @return void
 */
function block_smowl_bulk_check_instances($instances, $blockinstances) {
    global $DB;
    $coursecreateds = [];
    $coursedeleteds = [];
    $update = [];
    $courses = [];
    $coursesbi = [];
    // Manage instances.
    foreach ($instances as $instancepatt => $check) {
        $index = strpos($instancepatt, "-", 4);
        $pagetypepattern = substr($instancepatt, 0, $index + 1);
        $index2 = strpos($instancepatt, "-", $index + 1);
        $instanceid = substr($instancepatt, $index + 1, $index2 - $index - 1);
        $courseid = substr($instancepatt, $index2 + 1);
        if (!in_array($courseid, $courses)) {
            $courses[] = $courseid;
        }
        // Create or delete.
        if ($check) {
            if (!isset($blockinstances[$instanceid]) || empty($blockinstances[$instanceid])) {
                $params = ['instanceid' => $instanceid];
                $params['contextlevel'] = CONTEXT_MODULE;
                $modcontext = $DB->get_record('context', $params);
                if (!in_array($courseid, $coursesbi)) {
                    $cbi = block_smowl_check_course_block($courseid);
                    if (isset($cbi->id)) {
                        $coursesbi[$courseid] = $courseid;
                    } else {
                        $cbi = block_smowl_create_course_instance($courseid);
                        $coursesbi[$courseid] = $courseid;
                    }
                }
                // Create instance.
                $bi = block_smowl_create_instance($modcontext->id, $pagetypepattern);
                $update[] = $instanceid;
                // Create instance event.
                $event = \block_smowl\event\instance_created::create_instance($modcontext->id, $bi);
                $event->trigger();
            }
            $coursecreateds[$courseid][] = $instanceid;
        } else {
            if (isset($blockinstances[$instanceid])) {
                block_smowl_delete_instance($blockinstances[$instanceid]);
                $coursedeleteds[$courseid][$instanceid] = $blockinstances[$instanceid];
            }
        }
    }

    // Update quiz view blocks.
    block_smowl_update_quiz_blocks($update);

    // Create Post for course.
    foreach ($courses as $cid) {
        $created = [];
        if (isset($coursecreateds[$cid])) {
            $created = $coursecreateds[$cid];
        }
        $deleted = [];
        if (isset($coursedeleteds[$cid])) {
            $deleted = $coursedeleteds[$cid];
        }
        // Post with createds and deleteds.
        block_smowl_post_instances($created, $deleted, $cid);
    }
}

/**
 * Check if will create or delete instances
 * @see $DB
 * @see block_smowl_get_instances()
 * @param array $update to update
 * @return void
 */
function block_smowl_update_quiz_blocks($update) {
    global $DB;

    $blockinstances = block_smowl_get_instances(0, $update);
    foreach ($blockinstances as $instance) {
        if ($instance->name != 'quiz') {
            continue;
        }
        $params = [
            'id' => $instance->moduleinstance,
            'showblocks' => 0,
        ];
        $record = $DB->get_record('quiz', $params);
        if (!empty($record)) {
            $record->showblocks = 1;
            $DB->update_record('quiz', $record);
        }
    }
}

/**
 * Create block instance
 * @see $DB
 * @see block_smowl_create_instance()
 * @param int $contextid
 * @param string $pagetypepattern
 * @return int
 */
function block_smowl_create_instance($contextid, $pagetypepattern) {
    global $DB;
    if ($pagetypepattern == 'mod-quiz-') {
        block_smowl_create_instance($contextid, $pagetypepattern.'attempt');
        block_smowl_create_instance($contextid, $pagetypepattern.'summary');
        $pagetypepattern = 'mod-quiz-view';
    } else if ($pagetypepattern != 'mod-quiz-attempt' && $pagetypepattern != 'mod-quiz-summary') {
        $pagetypepattern .= '*';
    }

    $record = new stdClass();
    $record->blockname = 'smowl';
    $record->parentcontextid = $contextid;
    $record->showinsubcontexts = 0;
    $record->requiredbytheme = 0;
    $record->pagetypepattern = $pagetypepattern;
    $record->subpagepattern = '';
    $record->defaultregion = 'side-post';
    $record->defaultweight = 7;
    $record->configdata = null;
    $record->timecreated = time();
    $record->timemodified = time();

    return $DB->insert_record('block_instances', $record);
}

/**
 * Delete block instance
 * @see $DB
 * @see \block_smowl\event\instance_deleted::delete_instance
 * @param object $blockinstance
 * @return boolean
 */
function block_smowl_delete_instance($blockinstance) {
    try {
        if (!$blockinstance->context) { return true;
        }

        global $DB;
        if ($blockinstance->name == 'quiz') {
            foreach (['mod-quiz-attempt', 'mod-quiz-summary'] as $peerpatt) {
                $params = [
                    'parentcontextid' => $blockinstance->context,
                    'pagetypepattern' => $peerpatt,
                    'blockname' => 'smowl',
                ];
                $id = $DB->get_field('block_instances', 'id', $params);
                if ($id) {
                    $DB->delete_records('block_instances', ['id' => $id]);
                }
            }
        }
        // Delete instance event.
        $event = \block_smowl\event\instance_deleted::delete_instance(
            $blockinstance->context,
            $blockinstance->blockinstance
        );
        $return = $DB->delete_records('block_instances', ['id' => $blockinstance->blockinstance]);
        $event->trigger();

        return $return;
    } catch (Exception $e) {
        return true;
    }
}

/**
 * Create and make SMOWL post to report blocks changes
 * @see $DB
 * @see $COURSE
 * @see $CFG
 * @see context_course::instance()
 * @see block_smowl_get_instances()
 * @see block_smowl_get_instance_date()
 * @see block_smowl_check_entity()
 * @param array $created created instances
 * @param array $deleted deleted instances
 * @param int $courseid
 * @return void
 */
function block_smowl_post_instances($created = [], $deleted = [], $courseid = 0) {
    global $DB, $COURSE, $CFG;

    if (!block_smowl_check_entity()) {
        return;
    }
    if (!$courseid) {
        $courseid = $COURSE->id;
    }
    $entity = get_config('block_smowl', 'entity');
    $apikey = get_config('block_smowl', 'apikey');

    if (empty($created) && empty($deleted)) {
        return;
    }

    $context = context_course::instance($courseid);
    $sql = "SELECT COUNT(ra.id)
              FROM {role_assignments} ra
              JOIN {role_capabilities} rc ON rc.roleid = ra.roleid
             WHERE rc.capability = :capability AND ra.contextid = :contextid";
    $countparams = [
        'capability' => 'block/smowl:viewstudentcontent',
        'contextid' => $context->id,
    ];
    $countusers = $DB->count_records_sql($sql, $countparams);

    require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");
    $urlapi = block_smowl_connection::get_urlsmowlapi();
    $urladd = $urlapi . block_smowl_connection::get_apiaddactivity();
    $urlmod = $urlapi . block_smowl_connection::get_apimodifyactivity();

    // New instances.
    if (!empty($created)) {
        $news = block_smowl_get_instances(0, $created);
        foreach ($news as $new) {
            $params = [];
            $params['activityType'] = $new->name;
            $params['courseId'] = $new->course;
            $params['activityId'] = $new->moduleinstance;
            $params['moduleId'] = $new->coursemodule;
            $params['startDate'] = block_smowl_get_instance_date($new, 'timeopen');
            $params['endDate'] = block_smowl_get_instance_date($new, 'timeclose');
            $params['numberUsers'] = $countusers;
            $params['displayName'] = $DB->get_field($new->name, 'name', ['id' => $new->moduleinstance]);

            // Create new cURL.
            $curl = new curl();
            // Add options.
            $options = [
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_USERPWD' => $entity . ":" . $apikey,
            ];
            // Added proxy support
            if ($CFG->proxyhost) {
                $options['CURLOPT_PROXY'] = $CFG->proxyhost;
                $options['CURLOPT_PROXYPORT'] = $CFG->proxyport;
                if ($CFG->proxyuser && $CFG->proxypassword) {
                    $options['CURLOPT_PROXYUSERPWD'] = $CFG->proxyuser.':'.$CFG->proxypassword;
                }
            }

            // If the moodle is in a Proxy without web access.
            $content = $curl->post($urladd, $params, $options);
            $content = json_decode($content, true);

            if (isset($content['status']) && $content['status'] == 400 && $content['error'] == 400) {
                // Update activity.
                $params = [];
                $params['activityType'] = $new->name;
                $params['activityId'] = $new->moduleinstance;
                $params['enable'] = 'true';
                // If the moodle is in a Proxy without web access.
                $content = $curl->post($urlmod, $params, $options);
            }
        }
    }
    // Deleted instances.
    if (!empty($deleted)) {
        foreach ($deleted as $del) {
            $params = [];
            $params['activityType'] = $del->name;
            $params['activityId'] = $del->moduleinstance;
            $params['enable'] = 'false';
            // Create new cURL.
            $curl = new curl();
            // Add options.
            $options = [
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_USERPWD' => $entity . ":" . $apikey,
            ];
            // Added proxy support
            if ($CFG->proxyhost) {
                $options['CURLOPT_PROXY'] = $CFG->proxyhost;
                $options['CURLOPT_PROXYPORT'] = $CFG->proxyport;
                if ($CFG->proxyuser && $CFG->proxypassword) {
                    $options['CURLOPT_PROXYUSERPWD'] = $CFG->proxyuser.':'.$CFG->proxypassword;
                }
            }
            // If the moodle is in a Proxy without web access.
            $content = $curl->post($urlmod, $params, $options);
        }
    }
}

/**
 * Get quiz date information
 * @see $DB
 * @param object $instance
 * @param string $date date type
 * @return int date
 */
function block_smowl_get_instance_date($instance, $date) {
    global $DB;
    if ($instance->name == 'quiz') {
        return $DB->get_field('quiz', $date, ['id' => $instance->moduleinstance]);
    }
    return 0;
}

/**
 * Get courses where the user is enroled with teacher permissions with block SMOWL
 * if param courseid not in courses list, return void.
 * @see $DB
 * @see has_capability()
 * @see enrol_get_my_courses()
 * @see context_course::instance()
 * @param int $courseid
 * @return array courses with block SMOWL
 */
function block_smowl_get_courses($courseid) {
    global $DB;
    $coursesin = '';
    $siteadmin = has_capability('moodle/site:config', context_system::instance());
    if (!$siteadmin) {
        $usercourses = enrol_get_my_courses('summary, summaryformat');
        foreach ($usercourses as $course) {
            $context = context_course::instance($course->id);
            if (!has_capability('block/smowl:manageactivities', $context)) {
                unset($usercourses[$course->id]);
            }
        }
        if (empty($usercourses)) {
            return [];
        }
        $coursesin = 'AND c.id IN ('. implode(',', array_keys($usercourses)) .')';
    }
    $params = [
        'blockname' => 'smowl',
        'siteid' => SITEID,
        'contextlevel' => CONTEXT_COURSE,
    ];
    // Course select.
    $sql = "SELECT c.*
              FROM {block_instances} bi
              JOIN {context} ct ON bi.parentcontextid = ct.id AND ct.contextlevel = :contextlevel
              JOIN {course} c ON ct.instanceid = c.id
             WHERE bi.blockname = :blockname AND c.id != :siteid  $coursesin
          ORDER BY c.id ASC";
    $courses = $DB->get_records_sql($sql, $params);
    if (!in_array($courseid, array_keys($courses))) {
        return [];
    }
    return $courses;
}


/**
 * Check that activities has only 1 block smowl and delete if is necesary.
 * @see $DB
 * @see block_smowl_delete_instance()
 * @param int $courseid
 * @return void
 */
function block_smowl_check_only_once($courseid = 0) {
    global $DB;
    // Select SMOWL block_instance if exists.
    $params = [
        'blockname' => 'smowl',
        'contextlevel' => CONTEXT_MODULE,
    ];
    $in = '';
    if ($courseid) {
        $params['courseid'] = $courseid;
        $in = 'AND cm.course = :courseid';
    }

    $sql = "SELECT bi.*, bi.id as blockinstance, m.name
              FROM {block_instances} bi
              JOIN {context} c ON bi.parentcontextid = c.id AND c.contextlevel = :contextlevel
              JOIN {course_modules} cm ON c.instanceid = cm.id $in
              JOIN {modules} m ON m.id = cm.module
             WHERE bi.blockname = :blockname";
    $instances = $DB->get_records_sql($sql, $params);

    $parentcontexts = [];
    foreach ($instances as $instance) {
        if (in_array($instance->parentcontextid, array_keys($parentcontexts)) &&
                $parentcontexts[$instance->parentcontextid] == $instance->pagetypepattern) {
            $instance->context = $instance->parentcontextid;
            block_smowl_delete_instance($instance);
        }
        $parentcontexts[$instance->parentcontextid] = $instance->pagetypepattern;
    }
}

/**
 * Check that course has only 1 block smowl and delete if is necesary.
 * @see $DB
 * @param int $courseid
 * @return void
 */
function block_smowl_check_only_once_course($courseid = 0) {
    global $DB;
    // Select SMOWL block_instance if exists.
    $params = [
        'blockname' => 'smowl',
        'courseid' => $courseid,
        'contextlevel' => CONTEXT_COURSE,
    ];
    // Course select.
    $sql = "SELECT bi.*
              FROM {block_instances} bi
              JOIN {context} c ON bi.parentcontextid = c.id AND c.contextlevel = :contextlevel
             WHERE bi.blockname = :blockname AND c.instanceid = :courseid
          ORDER BY bi.id";

    $instances = $DB->get_records_sql($sql, $params);
    $c = count($instances);
    if ($c > 1) {
        $i = 1;
        foreach ($instances as $instance) {
            if ($i == 1) {
                $i++;
                continue;
            }
            $DB->delete_records('block_instances', ['id' => $instance->id]);
            $i++;
        }
    }
}

/**
 * Check smowl logs vs smowl blocks instances and Return differences.
 * @see $DB
 * @see \core_analytics\manager::get_analytics_logstore()
 * @see \core_analytics\manager::get_events_select()
 * @see block_smowl_check_new_legacy_blocks()
 * @param object $blockinstances
 * @return array instances not logged
 */
function block_smowl_check_new_blocks($blockinstances) {
    global $CFG, $DB;
    $return = [];
    $cfglogstore = "logstore_standard"; // Moodle4.2 or newer.
    // We first try to find logs.
    if (file_exists($CFG->dirroot.'/analytics/classes/manager.php')) {
        $logstore = \core_analytics\manager::get_analytics_logstore();
        if (isset($CFG->branch) && (float)$CFG->branch < 402) {
            // Moodle3.5 or newer.
            $cfglogstore = get_config('analytics', 'logstore');
        }
        if ($cfglogstore == "logstore_standard") {
            foreach ($blockinstances as $instanceid => $instance) {
                $params = [
                    'eventname' => '\block_smowl\event\instance_created',
                    'component' => 'block_smowl',
                    'contextid' => $instance->context,
                    'contextinstanceid' => $instance->id,
                ];
                $select = "eventname = :eventname AND component = :component
                    AND contextid = :contextid AND contextinstanceid = :contextinstanceid";
                $events = $logstore->get_events_select($select, $params, '', 0, 1);
                if (empty($events)) {
                    $return[$instanceid] = $instance;
                }
            }
        } else if ($cfglogstore == "logstore_legacy") {
            foreach ($blockinstances as $instanceid => $instance) {
                $events = block_smowl_check_new_legacy_blocks($instance);
                if (empty($events)) {
                    $return[$instanceid] = $instance;
                }
            }
        }
    } else {
        // Older than Moodle3.5.
        foreach ($blockinstances as $instanceid => $instance) {
            $events = block_smowl_check_new_legacy_blocks($instance);
            if (empty($events)) {
                $return[$instanceid] = $instance;
            }
        }
    }
    return $return;
}

/**
 * search in legacy DB logs
 * @see $DB
 * @param object $instance
 * @return array courses with block SMOWL
 */
function block_smowl_check_new_legacy_blocks($instance) {
    global $DB;
    $params = [
        'course' => $instance->course,
        'module' => 'smowl',
        'blockinstance' => '%: '.$instance->blockinstance,
    ];
    $select = "SELECT *
                 FROM {log}
                WHERE course = :course AND
                      module = :module AND
                      info LIKE :blockinstance
             ORDER BY time DESC";

    $events = $DB->get_records_sql($select, $params);
    return $events;
}

/**
 * Check smowl logs vs smowl blocks for individual instance.
 * @see $DB, $CFG
 * @see \core_analytics\manager::get_analytics_logstore()
 * @see \core_analytics\manager::get_events_select()
 * @param int $id module id
 * @param int $context module context
 * @return boolean
 */
function block_smowl_check_new_block($id, $context) {
    global $CFG, $DB;
    // We first try to find logs.
    $cfglogstore = "logstore_standard"; // Moodle4.2 or newer.
    // We first try to find logs.
    if (file_exists($CFG->dirroot.'/analytics/classes/manager.php')) {
        // Moodle3.5 or newer.
        $logstore = \core_analytics\manager::get_analytics_logstore();
        if (isset($CFG->branch) && (float)$CFG->branch < 402) {
            $cfglogstore = get_config('analytics', 'logstore');
        }
        if ($cfglogstore == "logstore_standard") {
            $params = [
                'eventname' => '\block_smowl\event\instance_created',
                'component' => 'block_smowl',
                'contextid' => $context,
                'contextinstanceid' => $id,
            ];
            $select = "eventname = :eventname AND component = :component
                AND contextid = :contextid AND contextinstanceid = :contextinstanceid";
            $events = $logstore->get_events_select($select, $params, '', 0, 1);
            if (empty($events)) {
                return true;
            }
        } else if ($cfglogstore == "logstore_legacy") {
            $params = ['context' => $context];
            $sql = "SELECT cm.course
                        FROM {course_modules} cm
                        JOIN {context} c ON cm.id = c.instanceid
                        WHERE c.id = :context";
            $course = $DB->get_field_sql($sql, $params);
            $instance = new stdClass();
            $conditions = [
               'blockname' => 'smowl',
               'parentcontextid' => $context,
               'pagetypepattern' => 'mod-quiz-view',
            ];
            $bi = $DB->get_field('block_instances', 'id', $conditions);
            $instance->blockinstance = $bi;
            $instance->course = $course;
            $events = block_smowl_check_new_legacy_blocks($instance);
            if (empty($events)) {
                return true;
            }
        }
    } else {
        // Older than Moodle3.5.
        $params = ['context' => $context];
        $sql = "SELECT cm.course
                    FROM {course_modules} cm
                    JOIN {context} c ON cm.id = c.instanceid
                    WHERE c.id = :context";
        $course = $DB->get_field_sql($sql, $params);
        $conditions = [
            'blockname' => 'smowl',
            'parentcontextid' => $context,
            'pagetypepattern' => 'mod-quiz-view',
        ];
        $bi = $DB->get_field('block_instances', 'id', $conditions);
        $instance = new stdClass();
        $instance->blockinstance = $bi;
        $instance->course = $course;
        $events = block_smowl_check_new_legacy_blocks($instance);
        if (empty($events)) {
            return true;
        }
    }
    return false;
}

/**
 * Save group restriction data in course block.
 * @see $DB
 * @param object $bi block instance
 * @param object $mform formdata
 * @return void
 */
function block_smowl_save_group_restrictions($bi, $mform) {
    // Groups is encoded and stored in the block's configdata.
    global $DB;
    unset($mform->id);
    unset($mform->view);
    unset($mform->submitbutton);
    $updateconfigdata = base64_encode(serialize($mform));
    if ($bi->configdata != $updateconfigdata) {
        $bi->configdata = $updateconfigdata;
        $DB->update_record('block_instances', $bi);
    }
}

/**
 * Check group restrictions.
 * @see $CFG
 * @see \core_availability\availability_info_block()
 * @param object $block object block instance
 * @return boolean
 */
function block_smowl_check_group_restrictions($block) {
    global $CFG;
    require_once($CFG->dirroot.'/blocks/smowl/classes/availability_info_block.php');
    $ci = new availability_info_block($block);
    if (!$ci->is_available($information, true)) {
        return false;
    }
    return true;
}

/**
 * Check modules to bulk actions.
 * @see $CFG, $DB
 * @param array $courses
 * @param string $modulename
 * @param string $activityname
 * @return array
 */
function block_smowl_bulkactive_search_modules($courses, $modulename, $activityname = '') {
    global $DB, $CFG;

    // Discriminate by type of activity in the courses.
    $in = 'AND cm.course IN ('.implode(',', array_keys($courses)).')';
    $moduleid = $DB->get_field('modules', 'id', ['name' => $modulename]);
    $params = [];
    $params['moduleid'] = $moduleid;
    $like = '';
    // Discriminate by name in the types of activity.
    if ($activityname != '') {
        $params['activityname'] = '%'.$activityname.'%';
        $like = "AND mn.name like :activityname";
    }

    $sql = "SELECT cm.id, cm.id AS coursemodule, cm.instance AS moduleinstance, cm.course, mn.name
              FROM {course_modules} cm
              JOIN {modules} m ON m.id = cm.module $in
              JOIN {{$modulename}} mn ON mn.id = cm.instance
             WHERE m.id = :moduleid AND cm.deletioninprogress = 0
                   $like
          ORDER BY cm.course ASC";
    return $DB->get_records_sql($sql, $params);
}

/**
 * Bulkgroups Search.
 * @see $DB
 * @param array $courses
 * @param string $groupname
 * @return array Groups
 */
function block_smowl_bulkgroups_search($courses, $groupname) {
    global $DB;

    // Discriminate by groups in the courses.
    $sql = 'courseid IN ('.implode(',', array_keys($courses)).')';
    $params = [];
    // Discriminate by name in the groups.
    if ($groupname != '') {
        $params['groupname'] = '%'.$groupname.'%';
        $sql .= " AND name like :groupname";
    }

    $groups = $DB->get_records_select('groups', $sql, $params);

    return $groups;
}

/**
 * Bulkgroups Search by selecteds.
 * @see $DB
 * @param array $courses
 * @param array $selectedgroups selected idgroups
 * @return array groups
 */
function block_smowl_bulkgroups_selected_groups($courses, $selectedgroups) {
    global $DB;

    // Name of the selected groups.
    $sql = 'id IN ('.implode(',', array_values($selectedgroups)).')';
    $groupsnames = $DB->get_records_select('groups', $sql, null, null, 'name');

    // Groups with this names in select groups.
    $sql = 'courseid IN ('.implode(',', array_keys($courses)).')
    AND name IN ("'.implode('","', array_keys($groupsnames)).'")';

    $groups = $DB->get_records_select('groups', $sql);

    return $groups;
}

/**
 * Return courseblock from idcourse.
 * @see $DB
 * @param int $courseid
 * @return array gropu
 */
function block_smowl_get_course_block($courseid) {
    global $DB;
    $params = [
        'blockname' => 'smowl',
        'contextlevel' => CONTEXT_COURSE,
    ];

    $in = " = :courseid";
    $params['courseid'] = $courseid;
    // Course select.
    $sql = "SELECT bi.*, c.instanceid AS courseid
              FROM {block_instances} bi
              JOIN {context} c ON bi.parentcontextid = c.id AND c.contextlevel = :contextlevel
             WHERE bi.blockname = :blockname AND c.instanceid $in";
    return $DB->get_record_sql($sql, $params);
}

/**
 * Return groups checkeds in block instance.
 * @see $DB
 * @see block_smowl_get_course_block()
 * @see block_smowl_bulkgroups_export_groups_config_blockinstance()
 * @param int $cid
 * @return object course groups configuration
 */
function block_smowl_bulkgroups_groups_config_blockinstance($cid) {
    global $DB;
    $cgroups = $DB->get_records('groups', ['courseid' => $cid]);
    $bi = block_smowl_get_course_block($cid);
    return block_smowl_bulkgroups_export_groups_config_blockinstance($bi, $cgroups);
}

/**
 * Return groups checkeds in block instance.
 * @param object $bi block instance
 * @param array $cgroups block instance
 * @return object course groups configuration
 */
function block_smowl_bulkgroups_export_groups_config_blockinstance($bi, $cgroups = false) {
    $gconfig = new stdClass();
    $gconfig->enable = 0;
    $gconfig->groups = [];
    $gconfig->must = 1;
    $gconfig->and = 0;

    if (empty($bi) || $bi->configdata == '' || $bi->configdata == null) {
        if ($cgroups) {
            $gconfig->groups = array_keys($cgroups);
        }
        return $gconfig;
    }

    $data = unserialize(base64_decode($bi->configdata));
    if (!isset($data->availabilityconditionsjson)) {
        if ($cgroups) {
            $gconfig->groups = array_keys($cgroups);
        }
        return $gconfig;
    }
    $jsconfig = json_decode($data->availabilityconditionsjson);
    if (!isset($jsconfig->c)) {
        if ($cgroups) {
            $gconfig->groups = array_keys($cgroups);
        }
        return $gconfig;
    }
    $gconfig->enable = 1;

    switch ($jsconfig->op) {
        case '!&':
            $gconfig->must = 0;
            $gconfig->and = 1;
            break;
        case '&':
            $gconfig->must = 1;
            $gconfig->and = 1;
            break;
        case '!|':
            $gconfig->must = 0;
            $gconfig->and = 0;
            break;
        case '|':
            $gconfig->must = 1;
            $gconfig->and = 0;
            break;
        default:
            break;
    }
    foreach ($jsconfig->c as $gactives) {
        if (isset($gactives->type) && $gactives->type == 'group') {
            if (isset($gactives->id)) {
                $gconfig->groups[] = $gactives->id;
            } else {
                if ($cgroups) {
                    $gconfig->groups = array_keys($cgroups);
                }
                return $gconfig;
                break;
            }
        }
    }
    if (isset($jsconfig->show)) {
        $gconfig->show = $jsconfig->show;
    }
    if (isset($jsconfig->showc)) {
        $gconfig->showc = $jsconfig->showc;
    }
    return $gconfig;
}

/**
 * Create and save configdata in courses block instances
 * @see $DB
 * @see block_smowl_get_course_block
 * @see block_smowl_create_course_instance
 * @see block_smowl_course_save_strict_configdata
 * @param array $activegroups new course groups restrictions
 * @return void
 */
function block_smowl_bulkgroups_save_strict_configdata($activegroups) {
    global $DB;
    $courses = [];
    foreach ($activegroups as $group) {
        if (isset($courses[$group->courseid])) {
            $courses[$group->courseid]->groups[] = $group->id;
        } else {
            $bi = block_smowl_get_course_block($group->courseid);
            if (empty($bi)) {
                $bi = block_smowl_create_course_instance($group->courseid);
            }
            $course = new stdClass();
            $course->id = $group->courseid;
            $course->bi = $bi;
            $course->groups = [$group->id];
            $courses[$group->courseid] = $course;
        }
    }
    foreach ($courses as $course) {
        block_smowl_course_save_strict_configdata($course->bi, $course->groups);
    }
}

/**
 * Create and save configdata in courses block instances
 * @see $DB
 * @param object $bi from database
 * @param object $groups groups checkeds
 * @return void
 */
function block_smowl_course_save_strict_configdata($bi, $groups) {
    global $DB;

    $data = new stdClass();

    $data->op = '|';

    $c = [];
    $cid = 0;
    foreach ($groups as $groupid) {
        $c[$cid]['type'] = 'group';
        $c[$cid]['id'] = (int)$groupid;
        $cid ++;
    }
    $data->c = $c;
    $data->show = true;

    $objclass = new stdClass();
    $objclass->availabilityconditionsjson = json_encode($data);
    $bi->configdata = base64_encode(serialize($objclass));
    $DB->update_record('block_instances', $bi);
}

/**
 * Create block course instance
 * @see $DB
 * @param int $courseid
 * @return array
 */
function block_smowl_create_course_instance($courseid) {
    global $DB;

    $params = [
        'contextlevel' => CONTEXT_COURSE,
        'instanceid' => $courseid,
    ];
    $contextid = $DB->get_field('context', 'id', $params);

    $record = new stdClass();
    $record->blockname = 'smowl';
    $record->parentcontextid = $contextid;
    $record->showinsubcontexts = 0;
    $record->requiredbytheme = 0;
    $record->pagetypepattern = 'course-view-*';
    $record->subpagepattern = '';
    $record->defaultregion = 'side-post';
    $record->defaultweight = 7;
    $record->configdata = null;
    $record->timecreated = time();
    $record->timemodified = time();

    $record->id = $DB->insert_record('block_instances', $record);
    return $DB->get_record('block_instances', ['id' => $record->id]);
}

/**
 * Check that activity has an blick view instance and if not exist create.
 * @see $DB
 * @param int $instanceid
 * @return bool false if all ok, true to no shows content.
 */
function block_smowl_check_attempt_instance($instanceid) {
    global $DB;
    $bview = $DB->get_record('block_instances', ['id' => $instanceid]);
    $correctpatt = [];
    $correctpatt["mod-quiz-view"] = 0;
    $correctpatt[""] = 0;
    $return = true;
    if (strpos($bview->pagetypepattern, "mod-quiz-") === false) {
            return false;
    }
    $peers = ['mod-quiz-view', 'mod-quiz-attempt', 'mod-quiz-summary'];
    $mypatt = null;
    foreach ($peers as $patt) {
        if (strpos($bview->pagetypepattern, $patt) === 0) {
            $mypatt = $patt;
            break;
        }
    }
    if ($mypatt !== null) {
        $allpresent = true;
        foreach ($peers as $patt) {
            if ($patt === $mypatt) {
                continue;
            }
            $params = [
                'parentcontextid' => $bview->parentcontextid,
                'pagetypepattern' => $patt,
            ];
            if (empty($DB->get_record('block_instances', $params))) {
                $allpresent = false;
                break;
            }
        }
        if ($allpresent) {
            return false;
        }
        $return = false;
    }

    // We are in a wrong block (mod-quiz-* or other).
    $bipatt = ['mod-quiz-view' => 1, 'mod-quiz-attempt' => 1, 'mod-quiz-summary' => 1];
    $bidbok = [];
    $bidbko = [];
    $biquiz = $DB->get_records('block_instances', ['parentcontextid' => $bview->parentcontextid], 'pagetypepattern DESC');

    foreach ($biquiz as $bi) {
        if (in_array($bi->pagetypepattern, array_keys($bipatt))) {
            $bidbok[$bi->id] = $bi;
            unset($bipatt[$bi->pagetypepattern]);
        } else {
            $bidbko[$bi->id] = $bi;
        }
    }
    if (empty($bipatt)) {
        foreach ($bidbko as $bidel) {
            $DB->delete_records('block_instances', ['id' => $bidel->id]);
        }
    } else {
        $cbiko = count($bidbko);
        if (($cbiko + count($bidbok)) == 1 ) {
            $return = false;
        }
        $cbipatt = count($bipatt);
        $i = 0;
        if ($cbiko >= $cbipatt) {
            foreach ($bidbko as $ko) {
                if ($i < $cbipatt) {
                    $ko->pagetypepattern = array_keys($bipatt)[0];
                    unset($bipatt[$ko->pagetypepattern]);
                    $DB->update_record('block_instances', $ko);
                    $i++;
                } else {
                    $DB->delete_records('block_instances', ['id' => $ko->id]);
                }
            }
        } else {
            $dbpatt = new stdClass();

            foreach ($bipatt as $patt => $val) {

                if ($cbiko == 0 && !isset($dbpatt->blockname)) {
                    $dbpatt = array_shift($bidbok);
                }
                if ($i < $cbiko) {
                    $ko = array_shift($bidbko);
                    if ( !isset($dbpatt->blockname)) {
                        $dbpatt = $ko;
                    }
                    $ko->pagetypepattern = $patt;
                    $DB->update_record('block_instances', $ko);
                    $i++;
                } else {
                    unset($dbpatt->id);
                    unset($dbpatt->pagetypepattern);
                    $dbpatt->pagetypepattern = $patt;
                    $DB->insert_record('block_instances', $dbpatt);
                }
            }
        }
    }
    return $return;
}

/**
 * Functions to integrate mod/quiz/accessrules/smowlcheckcam in block
 */

/**
 * Check if isset accessrule smowlcheckcam and setting to use is active.
 * @see $CFG
 * @return boolean
 */
function block_smowl_accessrule_smowlcheckcam_is_active() {
    global $CFG;
    $issetaccesrulecheckcam = file_exists($CFG->dirroot.'/mod/quiz/accessrule/smowlcheckcam/rule.php');
    $accesrulecheckcamactive = get_config('block_smowl', 'accesrulesmowlcheckcam');
    if ($issetaccesrulecheckcam && $accesrulecheckcamactive) {
        return true;
    }
    return false;
}

/**
 * Check if isset accessrule smowlcheckcam and setting to use is active.
 * @see $DB
 * @param int $cmid
 * @return int quizid
 */
function block_smowl_accessrule_smowlcheckcam_get_quizid($cmid) {
    global $DB;
    $params = [
        'cmid' => $cmid,
        'module' => "quiz",
    ];

    $sql = "SELECT cm.instance as id
              FROM {course_modules} cm
              JOIN {modules} m ON m.id = cm.module and m.name = :module
             WHERE cm.id = :cmid";
    $quizid = $DB->get_field_sql($sql, $params);
    return $quizid;
}

/**
 * Active accessrule smowlcheckcam accessrule at quiz.
 * @see $DB
 * @see block_smowl_accessrule_smowlcheckcam_get_quizid()
 * @see block_smowl_accessrule_smowlcheckcam_active_quiz_by_id()
 * @param int $instanceid to activate.
 * @return int id in quizaccess_smowlcheckcam.
 */
function block_smowl_accessrule_smowlcheckcam_active_quiz($instanceid) {
    $quizid = block_smowl_accessrule_smowlcheckcam_get_quizid($instanceid);
    block_smowl_accessrule_smowlcheckcam_active_quiz_by_id($quizid);
}

/**
 * Active accessrule smowlcheckcam accessrule at quiz by quizid.
 * @see $DB
 * @param int $quizid to activate.
 * @return int id in quizaccess_smowlcheckcam.
 */
function block_smowl_accessrule_smowlcheckcam_active_quiz_by_id($quizid) {
    global $DB;
    $quizaccessrule = $DB->get_record('quizaccess_smowlcheckcam', ['quizid' => $quizid]);
    if (!empty($quizaccessrule)) {
        if ($quizaccessrule->smowlcheckcamrequired != 1) {
            $quizaccessrule->smowlcheckcamrequired = 1;
            $DB->update_record('quizaccess_smowlcheckcam', $quizaccessrule);
        }
    } else {
        $record = new stdClass();
        $record->quizid = $quizid;
        $record->smowlcheckcamrequired = 1;
        return $DB->insert_record('quizaccess_smowlcheckcam', $record);
    }
}

/**
 * Disable accessrule smowlcheckcam accessrule at quiz.
 * @see $DB
 * @see block_smowl_accessrule_smowlcheckcam_get_quizid()
 * @param int $instanceid to activate.
 * @return void
 */
function block_smowl_accessrule_smowlcheckcam_disable_quiz($instanceid) {
    global $DB;
    $quizid = block_smowl_accessrule_smowlcheckcam_get_quizid($instanceid);
    $quizaccessrule = $DB->get_record('quizaccess_smowlcheckcam', ['quizid' => $quizid]);
    if (!empty($quizaccessrule)) {
        $DB->delete_records('quizaccess_smowlcheckcam', ['quizid' => $quizid]);
    }
}

/**
 * Active accessrule smowlcheckcam in all quizes with SMOWL.
 * @see $DB
 * @see block_smowl_get_instances()
 * @see block_smowl_accessrule_smowlcheckcam_active_quiz_by_id()
 */
function block_smowl_accessrule_smowlcheckcam_active_all() {
    global $DB;
    $blockinstances = block_smowl_get_instances();
    foreach ($blockinstances as $bi) {
        block_smowl_accessrule_smowlcheckcam_active_quiz_by_id($bi->moduleinstance);
    }
}

/**
 * Disable accessrule smowlcheckcam in all quizes with SMOWL.
 * @see $DB
 */
function block_smowl_accessrule_smowlcheckcam_disable_all() {
    global $DB;
    $DB->delete_records_select('quizaccess_smowlcheckcam', '');
}

/**
 * END Functions to integrate mod/quiz/accessrules/smowlcheckcam in block
 */

/**
 * Check if SMOWL settings has changed.
 * @see $CFG
 * @see block_smowl_prepare_lms_settings()
 */
function block_smowl_settings_check_changes() {
    global $CFG;

    $modifydate = get_config('block_smowl', 'modifydate');
    $apikey = get_config('block_smowl', 'apikey');
    if (date('YmdHis') - $modifydate < 20 || empty($apikey)) {
            return;
    }

    set_config('modifydate', date('YmdHis'), 'block_smowl');
    $settingssmowl = block_smowl_prepare_lms_settings();

    require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");
    block_smowl_connection::send_lms_settings($settingssmowl);
}

// Prepare API data to send.

/**
 * Prepare moodle LMS Settings object to send.
 * @param string $value information to send.
 */
function block_smowl_api_json_filter($value) {
    $value = urlencode($value);
    $value = str_replace('localhost', 'local.host', $value);
    $value = str_replace('%', 'smowlpercentage', $value);

    $jsonillegalcharpatterns = [
        '/\\$/',
        '/\</', '/\>/',
        '/\(/', '/\)/',
        '/\[/', '/\]/',
        '/\;/', '/\%/',
        '/\`/', '/\|/',
        '/\!/', '/\@/', '/\*/', '/\£/',
    ];
    $filter = preg_filter($jsonillegalcharpatterns, '', $value);
    if ($filter) {
        return $filter;
    }
    return $value;
}
/**
 * Prepare moodle LMS Settings object to send.
 * @see $CFG
 * @see $USER
 * @see $DB
 * @see block_smowl_api_json_filter()
 * @see block_smowl_connection::send_lms_settings()
 */
function block_smowl_prepare_lms_settings() {
    global $CFG, $USER, $DB;

    $smowlsettings = new stdClass();
    $smowlsettings->lmsUrl = block_smowl_api_json_filter($CFG->wwwroot);
    $smowlsettings->lmsRelease = block_smowl_api_json_filter($CFG->release);
    $smowlsettings->lmsVersion = block_smowl_api_json_filter($CFG->version);
    $smowlsettings->lmsUser = block_smowl_api_json_filter($USER->id);
    $smowlsettings->lmsLang = block_smowl_api_json_filter($CFG->lang);
    $smowlsettings->theme = block_smowl_api_json_filter($CFG->theme);

    $configs = $DB->get_records('config_plugins', ['plugin' => "block_smowl"]);
    foreach ($configs as $config) {
        $name = $config->name;
        switch ($config->name) {
            case 'version':
                $name = 'swlIntegrationVersion';
                break;
            case 'entity':
                $name = 'entityName';
                break;
            case 'instance':
                $name = 'entityInstance';
                break;
            case 'password':
                $name = 'swlLicenseKey';
                break;
            case 'apikey':
                $name = 'swlAPIKey';
                break;
            case 'smowljwtsecret':
                $name = 'secretJWT';
                break;
            case 'tracking':
                $name = 'tracking';
                break;
            case 'accesscontrol':
                $name = 'accessControl';
                break;
            case 'floatblock':
                $name = 'floatingBlock';
                break;
            case 'blockheight':
                $name = 'blockHeight';
                break;
            case 'viewsettingsinternal':
                $name = 'viewInternalSettings';
                break;
            case 'bulkactions':
                $name = 'bulkActions';
                break;
            case 'attempttracking':
                $name = 'attemptsDistinction';
                break;
            default:
                break;
        }
        $value = block_smowl_api_json_filter($config->value);
        if (empty($name)) {
            continue;
        }
        $smowlsettings->$name = $value;
    }

    // LTI TOOL y WS.

    require_once($CFG->dirroot.'/mod/lti/locallib.php');

    $ltitypeid = get_config('block_smowl', 'ltitypeid');
    $host = $CFG->wwwroot;
    if ($ltitypeid) {

        $ltitype = lti_get_type($ltitypeid);
        $toolconfig = lti_get_type_config($ltitypeid);

        $ltiparams = [];
        $ltiparams["name"] = $ltitype->name;
        $ltiparams["client_id"] = $ltitype->clientid;
        $ltiparams["auth_login_url"] = block_smowl_api_json_filter($host . "/mod/lti/auth.php");
        $ltiparams["auth_token_url"] = block_smowl_api_json_filter($host . "/mod/lti/token.php");
        $ltiparams["key_set_url"] = block_smowl_api_json_filter($host . "/mod/lti/certs.php");
        $ltiparams["issuer"] = block_smowl_api_json_filter($host);
        $ltiparams["host"] = block_smowl_api_json_filter($host);
        $ltiparams["deployment_id"] = $ltitype->id;

        $smowlsettings->lti_config = $ltiparams;

    }
    $sql = "SELECT et.token
    FROM {external_tokens} et
        JOIN {external_services} es ON es.id = et.externalserviceid
    WHERE es.shortname = :shortname";

    $strictness = IGNORE_MISSING;
    $params = [];
    $params['shortname'] = 'SMOWL_SERVICE';
    $wsclient = $DB->get_record_sql($sql, $params, $strictness);

    if ($wsclient) {

        $rest = [];
        $rest["url"] = block_smowl_api_json_filter($host . "/webservice/rest/server.php");
        $rest["token"] = $wsclient->token;

        $wsparams = [];
        $wsparams["allow_not_quizzes"] = get_config('block_smowl', 'tracking');
        $wsparams["moodle_rest_configuration"] = $rest;

        $smowlsettings->ws_config = $wsparams;

    }

    return $smowlsettings;
}

/**
 * Read smowl client JSON from plugin and return data.
 * @see $CFG
 * @return object with JSON data or false.
 */
function block_smowl_get_client_config() {
    global $CFG;
    $jsonfile = $CFG->dirroot.'/blocks/smowl/smowl_config.json';

    if (!file_exists($jsonfile)) {
        return false;
    }
    $clientfile = file_get_contents($jsonfile);
    if (!$clientfile) {
        return false;
    }
    $clientdata = json_decode($clientfile);
    if (!$clientdata || !isset($clientdata->client_id) || !isset($clientdata->client_key)) {
        return new stdClass();
    }
    return $clientdata;
}

/**
 * Send client configuration request.
 * @see $CFG
 * @return boolean true if return have correct information and saved or return false.
 */
function block_smowl_api_send_client_config_request() {
    global $CFG;
    require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");
    $lmsurl = block_smowl_api_json_filter($CFG->wwwroot);
    $jsondata = block_smowl_connection::request_config_client($lmsurl);
    if (!$jsondata) {
        if (!get_config('block_smowl', 'autoconfigerror')) {
            set_config('autoconfigerror', 'contenterror', 'block_smowl');
        }
        return false;
    }

    if ($jsondata) {
        if (isset($jsondata->entity_name)
            && isset($jsondata->swl_api_key)
            && isset($jsondata->swl_license_key)
            && isset($jsondata->swl_jwt_secret))
            {
            block_smowl_auto_config_entity($jsondata);
            return true;
        } else {
            if (isset($jsondata->status) && $jsondata->status == 401) {
                set_config('autoconfigerror', strtolower($jsondata->messages), 'block_smowl');
                return false;
            } else if (isset($jsondata->status)) {
                set_config('autoconfigerror', strtolower($jsondata->status), 'block_smowl');
                return false;
            } else {
                set_config('autoconfigerror', 'contenterror', 'block_smowl');
                return false;
            }
        }
    }
    set_config('autoconfigerror', 'contenterror', 'block_smowl');
    return false;
}

/**
 * Funtion to send settings to SMOWL or to customer entity data.
 * @param object $settings
 */
function block_smowl_send_lms_settings($settings) {
    global $CFG;
    require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");
    // Sent to SMOWL.
    $apirecived = block_smowl_connection::send_lms_settings($settings);
    if (!$apirecived) {
        // Send to customer.
        $apirecived = block_smowl_connection::send_lms_customer_settings($settings);
    }
}

/**
 * Config plugin from API JSON file.
 * @param object $jsondata information to set.
 */
function block_smowl_auto_config_entity($jsondata) {
    set_config('entity', $jsondata->entity_name, 'block_smowl');
    set_config('password', $jsondata->swl_license_key, 'block_smowl');
    set_config('apikey', $jsondata->swl_api_key, 'block_smowl');
    set_config('smowljwtsecret', $jsondata->swl_jwt_secret, 'block_smowl');
    // If we create new settings from SMOWL we add here.
}

/**
 * Clear preview Config plugin.
 * @return void.
 */
function block_smowl_clear_preview_config() {
    set_config('typeconfigprev', null, 'block_smowl');
    set_config('entityprev', null, 'block_smowl');
    set_config('passwordprev', null, 'block_smowl');
    set_config('apikeyprev', null, 'block_smowl');
    set_config('smowljwtsecretprev', null, 'block_smowl');
    set_config('clientidprev', null, 'block_smowl');
    set_config('clientkeyprev', null, 'block_smowl');
}

// LTI integration.

/**
 * Isset LTI Entity settings setted
 * Compare entity with ltientity
 * Si esta ok debe existir ltientity, ltitypeid y el ltideploymentid
 * si no están OK retornamos FALSE pq lo chechearemos al crear.
 * @return  boolean validation
 */
function block_smowl_lti_check() {
    $entity = get_config('block_smowl', 'entity');
    $ltientity = get_config('block_smowl', 'ltientity');

    if (!$ltientity) {
        set_config('ltientity', $entity, 'block_smowl');
        return false;
    }

    if ($entity != $ltientity && strcasecmp($ltientity, '') != 0 ) {
        block_smowl_lti_clean_types();
        set_config('ltientity', null, 'block_smowl');
        set_config('ltitypeid', null, 'block_smowl');
        set_config('ltideploymentid', null, 'block_smowl');
        set_config('ltiappid', null, 'block_smowl');
        return false;
    }

    $ltitypeid = get_config('block_smowl', 'ltitypeid');
    if (!$ltitypeid || !block_smowl_lti_check_type()) {
        set_config('ltitypeid', null, 'block_smowl');
        return false;
    }

    $ltideploymentid = get_config('block_smowl', 'ltideploymentid');
    if (strcasecmp($ltideploymentid, '') == 0) {
        set_config('ltideploymentid', null, 'block_smowl');
        return false;
    }

    $ltiappid = get_config('block_smowl', 'ltiappid');
    if (strcasecmp($ltiappid, '') == 0) {
        set_config('ltiappid', null, 'block_smowl');
        return false;
    }

    $ltirestid = get_config('block_smowl', 'ltirestid');
    if (strcasecmp($ltirestid, '') == 0 || !block_smowl_lti_check_token($ltirestid)) {
        set_config('ltirestid', null, 'block_smowl');
        return false;
    }

    return true;
}

/**
 * Delete LTI SMOOWL TOOLS.
 * @see $CFG
 * @param int $ltirestid check LTI rest id.
 * @return boolean true if return have correct information and saved or return false.
 */
function block_smowl_lti_check_token($ltirestid) {
    global $DB;
    if (!$ltirestid) {
        $ltirestid = get_config('block_smowl', 'ltirestid');
    }
    return $DB->get_field('external_tokens', 'id', ['token' => $ltirestid]);
}

/**
 * check is LTI TOOLS are visibles.
 * @see $CFG
 * @return boolean true if return have correct information and saved or return false.
 */
function block_smowl_lti_check_visible() {
    global $DB;
    return $DB->get_field('modules', 'visible', ['name' => 'lti']);
}

/**
 * Delete LTI SMOOWL TOOLS.
 * @see $CFG
 * @param int $nodelete id of tool to no delete.
 * @return boolean true if return have correct information and saved or return false.
 */
function block_smowl_lti_clean_types($nodelete = 0) {

    global $CFG, $DB;

    require_once($CFG->dirroot.'/blocks/smowl/classes/smowl_connection.php');

    $urlltitool = block_smowl_connection::get_urlsmowlltitool();
    $urlinit = $urlltitool . block_smowl_connection::get_ltitoolinit();

    $comparetext = $DB->sql_compare_text('baseurl')  . ' = ' . $DB->sql_compare_text(':baseurl');
    $sql = 'SELECT id FROM {lti_types} WHERE ' . $comparetext;

    $tools = $DB->get_records_sql($sql, ['baseurl' => $urlinit]);
    if (empty($tools)) {
        return false;
    }

    if (count($tools) == 1) {
        $tool = array_shift($tools);
        if ($tool->id == $nodelete) {
            return true;
        }
    }
    $return = false;
    require_once($CFG->dirroot.'/mod/lti/locallib.php');
    foreach ($tools as $tool) {
        if ($tool->id == $nodelete) {
            $return = true;
            continue;
        }
        lti_delete_type($tool->id);
    }
    $modulename = 'lti';
    $sql = "SELECT cm.*
        FROM {course_modules} cm
        JOIN {modules} md ON md.id = cm.module
        JOIN {lti} m ON m.id = cm.instance AND m.typeid NOT IN (SELECT id FROM {lti_types})
        WHERE md.name = :modulename";
    $params = [];
    $params['modulename'] = $modulename;
    $ltis = $DB->get_records_sql($sql, $params, IGNORE_MISSING);
    if (!$ltis) {
        return $return;
    }
    require_once("$CFG->dirroot/course/lib.php");
    foreach ($ltis as $lti) {
        course_delete_module($lti->id);
    }

    return $return;
}

/**
 * Check SMOWL LTI tool type
 * @see $CFG
 * @return boolean true if return have correct information and saved or return false.
 */
function block_smowl_lti_check_type() {

    global $CFG, $DB;
    $ltitypeid = get_config('block_smowl', 'ltitypeid');
    if (!$ltitypeid) {
        return false;
    }
    $return = block_smowl_lti_clean_types($ltitypeid);

    return $return;
}

/**
 * Create SMOWL LTI configuration.
 * block_smowl_lti_create_tool genera las variables:
 *  - ltientity
 *  - ltitypeid
 * @see $CFG
 * @return boolean true if return have correct information and saved or return false.
 */
function block_smowl_lti_create_tool() {
    global $CFG, $DB;
    if (block_smowl_lti_check_type()) {
        return get_config('block_smowl', 'ltitypeid');
    }

    require_once($CFG->dirroot.'/blocks/smowl/classes/smowl_connection.php');
    $urlltitool = block_smowl_connection::get_urlsmowlltitool();

    $data = new stdClass();
    $data->lti_typename = get_string('ltitoolname', 'block_smowl');
    $data->lti_toolurl = $urlltitool . block_smowl_connection::get_ltitoolinit();
    $data->lti_keytype = block_smowl_connection::get_default_ltitoolkeytype();
    $data->lti_publickeyset = $urlltitool . block_smowl_connection::get_ltitoolpublickeyset();
    $data->lti_initiatelogin = $urlltitool . block_smowl_connection::get_ltitoolinitiatelogin();
    $data->lti_redirectionuris = $urlltitool . block_smowl_connection::get_ltitoolredirection();
    $data->lti_coursevisible = block_smowl_connection::get_ltitoolconfigusage();
    $data->lti_launchcontainer = block_smowl_connection::get_ltitoollaunch();
    $data->lti_acceptgrades = 0;
    $data->lti_sendname = 1;
    $data->lti_sendemailaddr = 1;
    $data->ltiservice_memberships = block_smowl_connection::get_ltitoolconfigmemberships();

    require_once($CFG->dirroot.'/mod/lti/locallib.php');

    $type = new stdClass();
    $type->state = LTI_TOOL_STATE_CONFIGURED;
    $type->ltiversion = block_smowl_connection::get_ltitoolversion();

    $ltitoolid = lti_add_type($type, $data);

    // Update publickeyset.
    $ltitype = lti_get_type($ltitoolid);
    $ltitool = lti_get_type_config($ltitoolid);

    $ltitoolobj = new stdClass();
    if (isset($ltitool['publickeyset'])) {
        $ltitoolobj->typeid = $ltitype->id;
        $ltitoolobj->name = 'publickeyset';
        $ltitoolobj->value = $ltitool['publickeyset'] . '/' . $ltitype->clientid;
    }
    lti_update_config($ltitoolobj);
    $entity = get_config('block_smowl', 'entity');
    set_config('ltientity', $entity, 'block_smowl');
    set_config('ltitypeid', $ltitoolid, 'block_smowl');
    set_config('ltideploymentid', null, 'block_smowl');
    set_config('ltirestid', null, 'block_smowl');

}

/**
 * Send client configuration request.
 *  genera la variable:
 *  - ltideploymentid
 * @see $DB
 * @see $CFG
 * @return boolean true if return have correct information and saved or return false.
 */
function block_smowl_api_send_lti_ws_config() {
    global $DB, $CFG, $USER;

    $host = $CFG->wwwroot;

    $ltitypeid = get_config('block_smowl', 'ltitypeid');
    $ltideploymentid = get_config('block_smowl', 'ltideploymentid');
    $ltiappid = get_config('block_smowl', 'ltiappid');
    $request = 'GET';
    if (strcasecmp($ltideploymentid, '') == 0 || strcasecmp($ltiappid, '') == 0) {

        $ltirestid = get_config('block_smowl', 'ltirestid');

        if (!$ltitypeid) {
            set_config('lticonfigmessage', 'lticreatetoolerror', 'block_smowl');
            return false;
        }

        require_once($CFG->dirroot.'/mod/lti/locallib.php');
        $ltitype = lti_get_type($ltitypeid);

        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");
        if (!$ltidata = block_smowl_connection::send_lti_config($ltitype, $host, $request)) {
            return false;
        }
        // We have obtained the LTI configurations in SMOWL for this entity.
        if (!isset($ltidata->statusCode) || $ltidata->statusCode != 200 ) { // No conexión.
            $message = get_string('ltisendtoolerror', 'block_smowl') . ': ' . $request . ' GET BadReturn';
            set_config('lticonfigmessage', $message, 'block_smowl');
            return false;
        }

        $finded = 0;
        $request = 'POST';
        if (!empty($ltidata->data)) {
            foreach ($ltidata->data as $data) {
                if (isset($data->deployment)) {
                    $deployment = $data->deployment;
                    $ltientity = get_config('block_smowl', 'ltientity');
                    if ($data->issuer == $host) {
                        if ($deployment->entityId == $ltientity) {
                            set_config('ltideploymentid', $deployment->id, 'block_smowl');
                            set_config('ltiappid', $data->id, 'block_smowl');
                            if ($deployment->deploymentId == $ltitypeid) {
                                $finded = 1;
                                set_config('lticonfigmessage', 'lticreatetoolsuccess', 'block_smowl');
                            } else {
                                $request = 'PUT';
                            }
                            break;
                        }
                    }
                }
            }
        }

        if (!$finded) {
            // Call block_smowl_connection::send_lti_config by POST to save the data.
            if (!$ltidata = block_smowl_connection::send_lti_config($ltitype, $host, $request)) {
                return false;
            }

            if (!isset($ltidata->statusCode) || ($request == 'POST' && $ltidata->statusCode != 201) ||
                ($request == 'PUT' && $ltidata->statusCode != 200)) {
                $message = get_string('ltisendtoolerror', 'block_smowl') . ': ' . $request . ' BadReturn';
                if (isset($ltidata->statusCode)) {
                    $message .= ' ' . $ltidata->statusCode;
                }
                set_config('lticonfigmessage', $message, 'block_smowl');
                return false;
            }
            if (isset($ltidata->data)) {
                $data = $ltidata->data;
                if (isset($data->deployment)) {
                    $deployment = $data->deployment;
                    if ( $request == 'POST') {
                        set_config('ltideploymentid', $deployment->id, 'block_smowl');
                        set_config('ltiappid', $data->id, 'block_smowl');
                        set_config('lticonfigmessage', 'lticreatetoolsuccess', 'block_smowl');
                    }
                    set_config('lticonfigmessage', 'ltiupdatetoolsuccess', 'block_smowl');
                }
            }
        }
    }

    $ltirestid = get_config('block_smowl', 'ltirestid');
    if (!$ltirestid) {
        require_once($CFG->dirroot . '/webservice/lib.php');
        $webservicelib = new webservice();

        // 1. review if moodle ws are active.
        if (!$CFG->enablewebservices) {
            // 1.1 if not, activate
            set_config('enablewebservices', '1');
        }
        $servicename = 'SMOWL_SERVICE';
        $wsclient = $webservicelib->get_external_service_by_shortname($servicename);
        if (!empty($wsclient) && !$wsclient->enabled) {
            // 1.1 if not, activate
            $wsclient->enabled = 1;
            $webservicelib->update_external_service($wsclient);
        }
        // 1/2 Enable protocols

        // Enable REST server.
        $updateprotocol = false;
        $activeprotocols = empty($CFG->webserviceprotocols) ? [] : explode(',', $CFG->webserviceprotocols);
        if (!in_array('rest', $activeprotocols)) {
            $activeprotocols[] = 'rest';
            $updateprotocol = true;
        }
        if ($updateprotocol) {
            set_config('webserviceprotocols', implode(',', $activeprotocols));
        }

        // 2. check ws user & token.
        // 2.1 if user not exist
        $conditions = [
            'username' => 'smowlws',
            'mnethostid' => $CFG->mnet_localhost_id,
            'deleted' => 0,
        ];
        $userid = $DB->get_field('user', 'id', $conditions);
        if (!$userid) {
            require_once($CFG->dirroot . '/user/lib.php');
            $user = new stdClass;
            $user->username = 'smowlws';
            $user->firstname = 'Smowl';
            $user->lastname = 'Webservices User';
            $user->email = 'service.desk@smowltech.com';
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->auth = 'webservice';
            $user->confirmed = true;
            $userid = user_create_user($user, false);
        }

        if ($userid) {
            $user = $DB->get_record('user', ['id' => $userid]);
            if (user_not_fully_set_up($user)) {
                set_config('lticonfigmessage', 'lticreateusererror', 'block_smowl');
                return false;
            }
        }

        $roleid = $DB->get_field('role', 'id', ['shortname' => 'smowlwsrole']);
        if($roleid && $userid) {
            role_assign($roleid, $userid, context_system::instance());
        }

        $sql = "SELECT t.id, t.creatorid, t.token, u.firstname, u.lastname, s.id as wsid,
            s.name, s.enabled, s.restrictedusers, t.validuntil
            FROM {external_tokens} t, {user} u, {external_services} s
            WHERE t.tokentype = ".EXTERNAL_TOKEN_PERMANENT." AND
            s.id = t.externalserviceid AND t.userid = u.id AND u.id = :userid AND t.externalserviceid = :serviceid";

        $arrayparams = [];
        $arrayparams["userid"] = $userid;
        $arrayparams["serviceid"] = $wsclient->id;

        $tokens = $DB->get_records_sql($sql, $arrayparams);

        $token = '';
        if (!empty($tokens)) {
            $extoken = array_shift($tokens);
            $token = $extoken->token;
        } else {
            $context = context_system::instance();
            // Create token and activity.
            $token = bin2hex(random_bytes(16));
            $privatetoken = bin2hex(random_bytes(16));
            $record = new stdClass();
            $record->token = $token;
            $record->privatetoken = $privatetoken;
            $record->tokentype = EXTERNAL_TOKEN_PERMANENT;
            $record->userid = $userid;
            $record->externalserviceid = $wsclient->id;
            $record->contextid = $context->id;
            $record->creatorid = $USER->id;
            $record->validuntil = 0;
            $record->timecreated = time();
            $DB->insert_record('external_tokens', $record);
        }

        if (!$token) {
            set_config('lticonfigmessage', 'ltiactivewserror', 'block_smowl');
            return false;
        }

        $tracking = get_config('block_smowl', 'tracking');
        $arrayparams = [];
        $arrayparams["allow_not_quizzes"] = $tracking;
        if ($request == 'PUT') {
            $arrayparams["deployment_id"] = $ltitypeid;
        }

        $wsdata = block_smowl_connection::send_ws_config($token, $arrayparams, $host);
        if (!isset($wsdata->statusCode) || $wsdata->statusCode != 200) {
            set_config('lticonfigmessage', 'ltisendwserror', 'block_smowl');
            return false;
        }
        set_config('ltirestid', $token, 'block_smowl');
        return true;

    }
}


/**
 * Send client configuration request.
 *  genera la variable:
 *  - ltideploymentid
 * @see $DB
 * @see $CFG
 * @return boolean true if return have correct information and saved or return false.
 */
function block_smowl_api_send_lti_update_deployment() {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/webservice/lib.php');
    $webservicelib = new webservice();

    $servicename = 'SMOWL_SERVICE';
    $wsclient = $webservicelib->get_external_service_by_shortname($servicename);
    if (!empty($wsclient) && !$wsclient->enabled) {
        set_config('lticonfigmessage', 'ltisendwserror', 'block_smowl');
        return;
    }

    $sql = "SELECT t.id, t.creatorid, t.token, u.firstname, u.lastname, s.id as wsid,
    s.name, s.enabled, s.restrictedusers, t.validuntil
    FROM {external_tokens} t, {user} u, {external_services} s
    WHERE t.tokentype = ".EXTERNAL_TOKEN_PERMANENT." AND
    s.id = t.externalserviceid AND t.userid = u.id AND u.id = :userid AND t.externalserviceid = :serviceid";

    $conditions = [
        'username' => 'smowlws',
        'mnethostid' => $CFG->mnet_localhost_id,
        'deleted' => 0,
    ];

    $userid = $DB->get_field('user', 'id', $conditions);

    $arrayparams = [];
    $arrayparams["userid"] = $userid;
    $arrayparams["serviceid"] = $wsclient->id;

    $tokens = $DB->get_records_sql($sql, $arrayparams);
    $token = '';
    if (!empty($tokens)) {
        $extoken = array_shift($tokens);
        $token = $extoken->token;
    } else {
        set_config('lticonfigmessage', 'ltisendwserror', 'block_smowl');
        return;
    }
    $tracking = get_config('block_smowl', 'tracking');
    $ltitypeid = get_config('block_smowl', 'ltitypeid');

    $arrayparams = [];
    $arrayparams["allow_not_quizzes"] = $tracking;
    $arrayparams["deployment_id"] = $ltitypeid;

    $host = $CFG->wwwroot;

    $wsdata = block_smowl_connection::send_ws_config($token, $arrayparams, $host);
    if (!isset($wsdata->statusCode) || $wsdata->statusCode != 200) {
        set_config('lticonfigmessage', 'ltisendwserror', 'block_smowl');
        return;
    }
    set_config('ltirestid', $token, 'block_smowl');
}

/**
 * Isset LTI Entity settings setted
 * Compare entity with ltientity
 * Si esta ok debe existir ltientity, ltitypeid y el ltideploymentid
 * si no están OK retornamos FALSE pq lo chechearemos al crear.
 * @param int $courseid course id to check.
 * @return  boolean validation
 */
function block_smowl_lti_check_course($courseid) {
    global $DB, $CFG;
    $ltitypeid = get_config('block_smowl', 'ltitypeid');
    $modulename = "lti";
    $sql = "SELECT cm.*
    FROM {course_modules} cm
        JOIN {modules} md ON md.id = cm.module
        JOIN {".$modulename."} m ON m.id = cm.instance AND typeid = :ltitypeid
        WHERE cm.deletioninprogress = 0
        AND md.name = :modulename
        AND cm.course = :courseid";
    $params = [
        'modulename' => $modulename,
        'courseid' => $courseid,
        'ltitypeid' => $ltitypeid,
    ];
    $ltis = $DB->get_records_sql($sql, $params, IGNORE_MISSING);
    if (empty($ltis)) {
        return false;
    }
    $lti = array_shift($ltis);
    return $lti->id;
}

/**
 * Create SMOWL LTI configuration.
 * block_smowl_lti_create_course_tool genera las variables:
 *  - ltientity
 *  - ltitypeid
 * @see $CFG
 * @param int $courseid course id to create.
 * @return boolean true if return have correct information and saved or return false.
 */
function block_smowl_lti_create_course_tool($courseid) {
    global $DB, $CFG;

    $ltitypeid = get_config('block_smowl', 'ltitypeid');

    $modulename = "lti";

    $sql = "SELECT cm.*, m.name, md.name AS modname
        FROM {course_modules} cm
            JOIN {modules} md ON md.id = cm.module
            JOIN {".$modulename."} m ON m.id = cm.instance AND typeid = :ltitypeid
        WHERE cm.deletioninprogress = 0
        AND md.name = :modulename
        AND cm.course = :courseid";
    $params = [
        'modulename' => $modulename,
        'courseid' => $courseid,
        'ltitypeid' => $ltitypeid,
    ];
    $ltis = $DB->get_records_sql($sql, $params);

    if (!empty($ltis)) {
        return true;
    }

    require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

    $course = $DB->get_record('course', ['id' => $courseid]);

    $introeditor = [
        'text' => '',
        'format' => FORMAT_HTML,
        'itemid' => 0,
    ];

    $moduleinfo = new stdClass();
    $moduleinfo->introeditor = $introeditor;
    $moduleinfo->name = get_string('ltiactivityname', 'block_smowl');
    $moduleinfo->showdescription = 0;
    $moduleinfo->showtitlelaunch = 1;
    $moduleinfo->typeid = $ltitypeid;
    $moduleinfo->urlmatchedtypeid = $ltitypeid;
    $moduleinfo->launchcontainer = block_smowl_connection::get_ltitoollaunch();
    $moduleinfo->instructorchoiceacceptgrades = 0;
    $moduleinfo->visible = 1;
    $moduleinfo->visibleoncoursepage = 0;
    $moduleinfo->course = $courseid;
    $moduleinfo->coursemodule = 0;
    $moduleinfo->section = 0;
    $moduleinfo->module = $DB->get_field('modules', 'id', ['name' => $modulename]);
    $moduleinfo->modulename = 'lti';
    $moduleinfo->instance = 0;
    $moduleinfo->add = 'lti';
    $moduleinfo->update = 0;

    require_once("$CFG->dirroot/course/lib.php");
    $modinfo = create_module($moduleinfo);

    return $modinfo->instance;
}