<?php

use core\event\base;
use core\log\sql_reader;
use core\notification;
use logstore_standard\log\store;
use report_fundae\reportbuilder\local\api;

defined('MOODLE_INTERNAL') || die();
global $CFG;
if (file_exists($CFG->dirroot . '/mod/zoom/locallib.php')) {
    require_once($CFG->dirroot . '/mod/zoom/locallib.php');
}

/**
 * @throws coding_exception
 * @throws dml_exception
 */
function warn_if_cron_disabled(): void {
    $now = new DateTime();
    $lastcron = (int) get_config('tool_task', 'lastcronstart');
    $lastcron = new DateTime("@$lastcron");
    $iscrondisabled = 24 < (int) $now->diff($lastcron)->format('h');
    if ($iscrondisabled) {
        notification::warning(get_string('unabletogeneratereports', 'report_fundae'));
    }
}

/**
 * @param int $courseid
 * @param int $userid
 * @param string $sortby
 * @param int $limitfrom
 * @param int $limitnum
 * @return array
 * @throws dml_exception
 * @throws moodle_exception
 */
function get_activities_stats(int $courseid, int $userid, string $sortby = '', int $limitfrom = 0,
                              int $limitnum = 0): array {
    global $DB, $CFG;
    $fastmodinfo = get_fast_modinfo($courseid);
    $user = core_user::get_user($userid);
    if (!$fastmodinfo || !$user) {
        return [];
    }
    $cms = $fastmodinfo->get_cms();
    if (empty($cms)) {
        return [];
    }

    $activities = array_combine(array_keys($cms), array_map(static function($cm) {
        /** @var cm_info $cm */
        return $cm->get_course_module_record(true);
    }, $cms));
    $activities = array_slice($activities, $limitfrom, $limitnum > 0 ? $limitnum : null, true);
    $scormids = [];
    foreach ($cms as $cm) {
        if ($cm->modname === 'label') {
            unset($activities[$cm->id]);
            continue;
        }
        if (!isset($activities[$cm->id])) {
            continue;
        }
        if ($cm->modname === 'scorm') {
            $scormids[$cm->id] = $cm->instance;
        }
        $activities[$cm->id]->timeelapsed = 0;
        $activities[$cm->id]->screentime = 0;
        $activities[$cm->id]->engagedtime = 0;
        $activities[$cm->id]->hits = 0;
        $activities[$cm->id]->firstaccess = 0;
        $activities[$cm->id]->lastaccess = 0;
        $activities[$cm->id]->userid = $user->id;
        $activities[$cm->id]->urlevent = get_url_event($cm);
    }

    $readers = get_log_manager()->get_readers(sql_reader::class);
    $reader = reset($readers);
    if (empty($reader)) {
        return $activities;
    }
    if (!($reader instanceof store)) {
        return $activities;
    }
    if (file_exists($CFG->dirroot . '/mod/zoom/locallib.php')) {
        foreach ($activities as $key => $activity) {
            if ($activity->modname === 'zoom') {
                $zoom = $DB->get_record('zoom', ['course' => $courseid, 'id' => $activity->instance], 'id, meeting_id');
                $sessions = zoom_get_sessions_for_display($zoom->id);
                $usertimes = 0;
                $firstsession = false;
                foreach ($sessions as $session) {
                    foreach ($session['participants'] as $participant) {
                        if ((int)$participant->userid === (int)$user->id) {
                            $usertimes += $participant->duration;
                            ++$activities[$key]->hits;
                            if ($firstsession === false) {
                                $activities[$key]->firstaccess = $participant->join_time;
                                $firstsession = true;
                            }
                            $activities[$key]->lastaccess = $participant->join_time;
                        }
                    }
                }
                $activities[$key]->timeelapsed = $usertimes;
            }
        }
    }
    if ((int)get_config('report_fundae', 'bbbreports') === 1) {
        foreach ($activities as $key => $activity) {
            if ($activity->modname === 'bigbluebuttonbn') {
                $bbb = $DB->get_record('bigbluebuttonbn', ['course' => $courseid, 'id' => $activity->instance]);
                $api = new api($user->id, $courseid);
                $datauserbbb = $api->calculate_user_bbb_forcm($bbb->id, $user->id);
                $activities[$key]->hits = $datauserbbb['numrecordings'];
                $activities[$key]->timeelapsed = $datauserbbb['usercmtime'];
            }
        }
    }
    $now = time();
    $threshold = (int) get_config('report_fundae', 'sessionthreshold');
    $sessionthreshold = $threshold * MINSECS;
    $course = $fastmodinfo->get_course();
    $mintime = $course->startdate;
    $maxtime = (!$course->enddate || $now < $course->enddate) ? $now : $course->enddate;
    $wheresql = " origin <> 'cli' AND timecreated >= :mintime AND timecreated <= :maxtime" .
        ' AND courseid = :courseid AND userid = :userid AND edulevel = :edulevel ';
    $params = ['courseid' => $course->id, 'mintime' => $mintime, 'maxtime' => $maxtime,
        'userid' => $user->id, 'edulevel' => base::LEVEL_PARTICIPATING];
    /** @var base[] $logs */
    $logs = $reader->get_events_select($wheresql, $params, 'timecreated ASC', 0, 0);
    if (empty($logs)) {
        return $activities;
    }
    $prevlog = array_shift($logs);
    $prevlogtime = $prevlog->timecreated;
    $prevlogcmid = CONTEXT_MODULE === (int) $prevlog->contextlevel ? (int) $prevlog->contextinstanceid : 0;
    if ($prevlogcmid) {
        ++$activities[$prevlogcmid]->hits;
        $activities[$prevlogcmid]->firstaccess = $prevlog->timecreated;
        $activities[$prevlogcmid]->lastaccess = $prevlog->timecreated;
    }
    foreach ($logs as $log) {
        $logtime = (int)$log->timecreated;
        $logcmid = CONTEXT_MODULE === (int)$log->contextlevel ? (int)$log->contextinstanceid : 0;
        if (!isset($activities[$logcmid])) {
            continue;
        }
        if ($logcmid) {
            ++$activities[$logcmid]->hits;
            $activities[$logcmid]->lastaccess = $logtime;
            if (empty($activities[$logcmid]->firstaccess)) {
                $activities[$logcmid]->firstaccess = $logtime;
            }
        }
        $newsessionstarted = ($logtime - $prevlogtime) > $sessionthreshold;
        if ($prevlogcmid && !$newsessionstarted) {
            $elapsedtime = $logtime - $prevlogtime;
            $activities[$prevlogcmid]->timeelapsed += $elapsedtime;
        }
        if ($logcmid && $log->action === 'tracked') {
            if ($log->target === 'engagedtime') {
                $activities[$logcmid]->engagedtime += $log->other['engagedtimeadded'];
            } else if ($log->target === 'screentime') {
                $activities[$logcmid]->screentime += $log->other['screentimeadded'];
            }
        }
        $prevlogcmid = CONTEXT_MODULE === (int) $log->contextlevel ? (int) $log->contextinstanceid : 0;
        $prevlogtime = $logtime;
    }
    // Only scorms.
    if (!empty($scormids)) {
        foreach ($scormids as $cmid => $instanceid) {
            [$time, $scormstracked] = api::calculate_user_scorm_times_and_tracked([$instanceid], $userid, $mintime, $maxtime);
            // Some scorms have no tracking.
            if ((int)$time !== 0) {
                $activities[$cmid]->timeelapsed = $time;
            }
        }
    }
    return $activities;
}

/**
 * @param cm_info $cm
 * @return string
 * @throws moodle_exception
 */
function get_url_event(cm_info $cm): string {
    return (new moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cm->id]))->out(false);
}


function format_report_time(int $timevalue): string {
    if (!$timevalue) {
        return '0s';
    }

    $secs = $timevalue % 60;
    $mins = floor($timevalue / 60);
    $hours = floor($mins / 60);
    $mins %= 60;
    if ($hours > 0) {
        return "{$hours}h {$mins}m {$secs}s";
    }
    if ($mins > 0) {
        return "{$mins}m {$secs}s";
    }
    return "{$secs}s";
}
