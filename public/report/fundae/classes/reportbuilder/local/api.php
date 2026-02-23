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
namespace report_fundae\reportbuilder\local;

use cache;
use cm_info;
use coding_exception;
use context_course;
use context_module;
use core\event\base;
use core\log\sql_reader;
use core_completion\progress;
use core_user;
use curl;
use DateTime;
use dml_exception;
use DOMDocument;
use Exception;
use JsonException;
use logstore_standard\log\store;
use mod_bigbluebuttonbn\local\config;
use mod_bigbluebuttonbn\local\proxy\proxy_base;
use moodle_exception;
use SimpleXMLElement;
use stdClass;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once $CFG->dirroot. '/report/fundae/locallib.php';
if (file_exists($CFG->dirroot . '/mod/zoom/locallib.php')) {
    require_once($CFG->dirroot . '/mod/zoom/locallib.php');
}
if (   str_contains($CFG->release, '4.3')
    || str_contains($CFG->release, '4.4')
    || str_contains($CFG->release, '4.5')
    || str_contains($CFG->release, '5.0')
    || str_contains($CFG->release, '5.1')
    ) {
    require_once $CFG->dirroot . '/mod/scorm/locallib.php';
}

/**
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

class api {

    private $reader;
    private int $userid;
    private int $courseid;
    private array $bbbdata = [];

    /**
     * api constructor.
     */
    public function __construct($userid, $courseid) {
        $this->userid = $userid;
        $this->courseid = $courseid;
        $readers = get_log_manager()->get_readers(sql_reader::class);
        $this->reader = reset($readers);
    }

    /**
     * @param string $selectwhere
     * @param array $params
     * @param string $sort
     * @param string $limitfrom
     * @param string $limitnum
     * @return array|null
     */
    private function get_events(string $selectwhere, array $params, string $sort, string $limitfrom, string $limitnum) : ?array {
        if (empty($this->reader)) {
            return null; // No supported log reader found.
        }
        if (!($this->reader instanceof store)) {
            return null; // Unsupported store instance, only standard log is supported.
        }

        return  $this->reader->get_events_select($selectwhere, $params, $sort, $limitfrom, $limitnum);
    }

    /**
     * @return int
     * @throws dml_exception
     */
    private function get_user_lastaccess_timestamp(): int {
        global $DB;
        $conditions = ['courseid' => $this->courseid, 'userid' => $this->userid];
        $data =  $DB->get_record('user_lastaccess', $conditions, 'timeaccess');
        return $data->{'timeaccess'} ?? 0;
    }

    /**
     * @return string
     * @throws dml_exception
     * @throws coding_exception
     */
    private function get_user_lastaccess() : string {
        global $DB;
        $conditions = ['courseid' => $this->courseid, 'userid' => $this->userid];
        $data =  $DB->get_record('user_lastaccess', $conditions, 'timeaccess');
        if (isset($data->{'timeaccess'})) {
            return userdate($data->{'timeaccess'}, get_string('strftimedatetimeshort'));
        }
        return '';
    }

    /**
     * @return object
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     * @throws Exception
     */
    public function get_cached_object(): object {
        global $DB, $CFG;
        // Some user info is cached.
        $cache = cache::make('report_fundae', 'usercourse');
        $key = 'fundae_';
        $usercoursekey = $this->userid . '_' . $this->courseid;
        $fundaejson = $cache->get($key . $usercoursekey); // array de userid_courseid
        if ($fundaejson !== false) {
            $cachedata = json_decode($fundaejson);
            $userlastaccess = $this->get_user_lastaccess_timestamp();
            if (isset($cachedata->{$usercoursekey}) && $userlastaccess < $cachedata->timecreated) {
                return $cachedata->{$usercoursekey};
            }
        }

        // Prepare to get messaging logs.
        [$teachersids, $studentids] = self::get_enrolled_users_by_course($this->courseid);

        // Generate course user info cache.
        $returnvalue = new stdClass();
        $course = get_course($this->courseid);
        $now = time();
        $mintime = $course->startdate; // Seconds.
        $maxtime = (!$course->enddate || $now < $course->enddate) ? $now : $course->enddate; // Seconds.
        $maxdate = new DateTime('@' . $maxtime);
        $mindate = new DateTime('@' . $mintime);
        $perioddays = 1 + (int) $maxdate->diff($mindate)->format('%a');

        $wheresql = " origin <> 'cli' AND timecreated >= :mintime AND timecreated <= :maxtime " .
            ' AND ( ( courseid = :courseid AND edulevel = :edulevel AND userid = :userid) ' .
            " OR ( action = 'sent' AND target = 'message' AND userid = :userid2  ) ) ";
        $params = [
            'mintime' => (int)$mintime,
            'maxtime' => $maxtime,
            'courseid' => $this->courseid,
            'edulevel' => base::LEVEL_PARTICIPATING,
            'userid' => $this->userid,
            'userid2' => $this->userid
        ];
        $userlogs = $this->get_events($wheresql, $params, 'timecreated ASC', 0, 0);

        // Initial values.
        $threshold = get_config('report_fundae', 'sessionthreshold');
        $sessionthreshold = $threshold * MINSECS; // Convert session threshold from minutes to seconds.
        $fastmodinfo = get_fast_modinfo($this->courseid);
        /** @var cm_info[] $cms */
        $cms = $fastmodinfo ? $fastmodinfo->get_cms() : [];
        $cmscorms = array_filter($cms, static function($cm) {
            return $cm->modname === 'scorm';
        });
        $cmlessons = array_filter($cms, static function($cm) {
            return $cm->modname === 'lesson';
        });
        $scormsids = array_column($cmscorms, 'instance');
        $lessonsids = array_column($cmlessons, 'instance');
        // Fetch SCORM times separately (SCORMS have their own total activity time recorded).
        [$totalscormtime, $scormstracked] = self::calculate_user_scorm_times_and_tracked($scormsids, $this->userid, $mintime, $maxtime);

        // Fetch Lesson times to add them to activity time (lessons may have several attempts, and time is required for all of them).
        $totallessootime = self::calculate_user_lesson_times($lessonsids, $this->userid, $mintime, $maxtime);
        $activitiescmids = array_column($cms, 'id');
        $activitiestimeszeroes = array_combine($activitiescmids, array_fill(0, count($activitiescmids), 0));

        // Add user and course information to the user row.
        $progress = progress::get_course_progress_percentage($course, $this->userid);
        $returnvalue->percentage = is_null($progress) ? '0%' : round($progress, 2) . '%';

        // Default values (in case we find no logs for this user).
        $returnvalue->dedicationtime = format_report_time(0);
        $returnvalue->sessionscount = 0;
        $returnvalue->daysconnectedcount = 0;
        $returnvalue->connectionratio = 0;
        $returnvalue->screentime = format_report_time(0);
        $returnvalue->engagedtime = format_report_time(0);
        $returnvalue->firstaccess = '-';
        $returnvalue->lastaccess = $this->get_user_lastaccess() ?: '-';
        $returnvalue->totalscormtime = format_report_time(0);
        $returnvalue->totalzoomtime = format_report_time(0);
        $returnvalue->totalbbbtime = format_report_time(0);
        $returnvalue->totalactivitiestimes = format_report_time(0);
        $returnvalue->messagestostudentscount = 0;
        $returnvalue->messagestoteacherscount = 0;
        $returnvalue->recipientsstudentscount = 0;
        $returnvalue->recipientsteacherscount = 0;
        $returnvalue->coursestartdate = userdate($course->startdate, get_string('strftimedatetimeshort'));
        $returnvalue->courseenddate = (int)$course->enddate === 0 ? '-' : userdate($course->enddate, get_string('strftimedatetimeshort'));
        $zoomtime = 0;
        if (file_exists($CFG->dirroot . '/mod/zoom/locallib.php')) {
            foreach ($cms as $cm) {
                if ($cm->modname === 'zoom') {
                    $zoom = $DB->get_record('zoom', ['course' => $this->courseid, 'id' => $cm->instance], 'id');
                    $sessions = zoom_get_sessions_for_display($zoom->id);
                    foreach ($sessions as $session) {
                        foreach ($session['participants'] as $participant) {
                            if ((int)$participant->userid === $this->userid) {
                                $zoomtime += $participant->duration;
                            }
                        }
                    }
                }
            }
        }

        $bbbtime = 0;
        if ((int)get_config('report_fundae', 'bbbreports') === 1) {
            $bbbtime = $this->calculate_user_bbb_totaltimes($this->userid);
        }

        if (empty($userlogs) && $zoomtime === 0 && $bbbtime === 0) {
            $userincoursearray['timecreated'] = time();
            $userincoursearray[$usercoursekey] = $returnvalue;
            $cache->set($key . $usercoursekey, json_encode($userincoursearray));
            return $returnvalue;
        }

        // Get initial data from the first log.
        $prevlog = array_shift($userlogs);
        $prevlogtime = $prevlog->timecreated ?? 0;
        $prevlogcmid = 0;
        if (isset($prevlog->contextinstanceid)) {
            $prevlogcmid = CONTEXT_MODULE === (int) $prevlog->contextlevel ? (int) $prevlog->contextinstanceid : 0;
        }

        $firstaccess = $prevlogtime;
        $sessionstart = $prevlog->timecreated ?? 0;
        $totalsessionstime = 0;
        $daysconnected = [];
        if (isset($prevlog->timecreated)) {
            $daysconnected[date('Y-m-d', $prevlog->timecreated)] = 1;
        }
        $sessionstimes = [$totalsessionstime];
        $activitiestimes = $activitiestimeszeroes;
        $messagestostudents = 0;
        $messagestoteachers = 0;

        // Aggregate data using the rest of the logs.
        foreach ($userlogs as $userlog) {
            // Session times estimates.
            $currentlogtime = (int) $userlog->timecreated;
            $newsessionstarted = ($currentlogtime - $prevlogtime) > $sessionthreshold;
            if ($newsessionstarted) {
                $elapsedtime = $prevlogtime - $sessionstart;
                $totalsessionstime += $elapsedtime;
                $sessionstimes[] = $totalsessionstime;
                $sessionstart = $currentlogtime;
            }

            $istrackingscorm = false;
            if(array_key_exists($prevlogcmid, $cms) && $cms[$prevlogcmid]->modname === 'scorm') {
                $thiscormid = (int)$cms[$prevlogcmid]->instance;
                $istrackingscorm = array_key_exists($thiscormid, $scormstracked);
            }

            // Activities session times estimates.
            if ($prevlogcmid && !$newsessionstarted && !$istrackingscorm) {
                if (!isset($activitiestimes[$prevlogcmid])) {
                    $activitiestimes[$prevlogcmid] = 0;
                }
                $elapsedtime = $currentlogtime - $prevlogtime;
                $activitiestimes[$prevlogcmid] += $elapsedtime;
            }

            // Messaging aggregations.
            if ($userlog->action === 'sent' && $userlog->target === 'message' && $this->userid !== (int)$userlog->relateduserid) {
                if (in_array($userlog->relateduserid, $studentids, true)) {
                    $messagestostudents++;
                }
                if (in_array($userlog->relateduserid, $teachersids, true)) {
                    $messagestoteachers++;
                }
            }

            $prevlogcmid = CONTEXT_MODULE === (int) $userlog->contextlevel ? (int) $userlog->contextinstanceid : 0;
            $prevlogtime = $currentlogtime;
            $daysconnected[date('Y-m-d', $currentlogtime)] = 1;
        }

        $totalsessionstime += $prevlogtime - $sessionstart;
        $totalsessionstime += $zoomtime + $bbbtime;

        $dedicationtime = $totalsessionstime + $totalscormtime + $totallessootime + array_sum($activitiestimes);
        $returnvalue->dedicationtime = format_report_time($dedicationtime);
        $returnvalue->sessionscount = count($sessionstimes);
        $returnvalue->daysconnectedcount = count($daysconnected);
        $ratio = $returnvalue->daysconnectedcount / $perioddays;
        $returnvalue->connectionratio = $perioddays > 0 ? round($ratio, 2) : 0;
        $returnvalue->firstaccess = $firstaccess === 0 ? '-' : userdate($firstaccess, get_string('strftimedatetimeshort'));
        $returnvalue->totalscormtime = format_report_time($totalscormtime);
        $returnvalue->totalzoomtime = format_report_time($zoomtime);
        $returnvalue->totalbbbtime = format_report_time($bbbtime);
        $returnvalue->totalactivitiestimes = format_report_time(array_sum($activitiestimes) + $totalscormtime + $totallessootime + $zoomtime + $bbbtime);
        $returnvalue->messagestostudentscount = $messagestostudents;
        $returnvalue->messagestoteacherscount = $messagestoteachers;

        // Caché.
        $userincoursearray['timecreated'] = time();
        $userincoursearray[$usercoursekey] = $returnvalue;
        $cache->set($key . $usercoursekey, json_encode($userincoursearray, JSON_THROW_ON_ERROR));

        return $returnvalue;
    }
    /**
     * @param array $scormsids
     * @param int $userid
     * @param int $mintime
     * @param int $maxtime
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function calculate_user_scorm_times_and_tracked(array $scormsids, int $userid, int $mintime, int $maxtime): array {
        global $DB, $CFG;
        // Default values (all zeroes).
        $totalscormtime = 0;
        $scormstracked = [];
        if (empty($scormsids)) {
            return [$totalscormtime, $scormstracked];
        }

        // Get SCORM time track records related to this user.
        if (str_contains($CFG->release, '4.3') || str_contains($CFG->release, '4.4')  || str_contains($CFG->release, '4.5')) {
            foreach ($scormsids as $scormid) {
                $attempts = $DB->get_records('scorm_attempt', ['userid' => $userid, 'scormid' => $scormid], '', 'attempt');
                $scoes = $DB->get_records('scorm_scoes', ['scorm' => $scormid], 'sortorder, id');
                foreach ($attempts as $attempt) {
                    foreach ($scoes as $sco) {
                        if ($sco->launch !== '') {
                            $trackdata = scorm_get_tracks($sco->id, $userid, $attempt->attempt);
                            if ($trackdata !== false &&
                                isset($trackdata->total_time) &&
                                $trackdata->timemodified >= $mintime &&
                                $trackdata->timemodified <= $maxtime) {
                                    if (preg_match("/(\d\d):(\d\d):(\d\d)\./", $trackdata->total_time, $matches)) {
                                        $scormtime = $matches[1] * HOURSECS + $matches[2] * MINSECS + $matches[3];
                                        $totalscormtime += $scormtime;
                                    }
                            }
                        }
                        if ($totalscormtime !== 0) {
                            $scormstracked[$scormid] = $scormid;
                        }
                    }
                }
            }
        } else {
            [$insql, $inparams] = $DB->get_in_or_equal($scormsids);
            $sql = "element = 'cmi.core.total_time' AND scormid $insql AND userid = ? AND timemodified >= ? AND timemodified <= ?";
            $params = array_merge($inparams, [$userid, $mintime, $maxtime]);
            $tracks = $DB->get_records_select('scorm_scoes_track', $sql, $params, 'id,element,scormid,value');
            foreach ($tracks as $track) {
                if (preg_match("/(\d\d):(\d\d):(\d\d)\./", $track->value, $matches)) {
                    $scormtime = 0;
                    if (isset($matches[1], $matches[2], $matches[3])) {
                        $scormtime = $matches[1] * HOURSECS + $matches[2] * MINSECS + $matches[3];
                    }
                    if ($scormtime !== 0 && $scormtime !== null) {
                        $scormstracked[$track->scormid] = $track->scormid;
                    }
                    $totalscormtime += is_int($scormtime) ? $scormtime : 0;
                }
            }
        }
        return [$totalscormtime, $scormstracked];
    }

    /**
     * Logic obtained from the method lesson_get_overview_report_table_and_data of mod/lesson/locallib.php, adapted to all lessons for one user.
     * @param array $lessonsids
     * @param int $userid
     * @param int $mintime
     * @param int $maxtime
     * @return int
     * @throws dml_exception
     */
    public static function calculate_user_lesson_times(array $lessonsids, int $userid, int $mintime, int $maxtime): int {
        global $DB;
        if (empty($lessonsids)) {
            return 0;
        }
        $totallessonstime = 0;
        foreach ($lessonsids as $lessonsid) {
            $studentdata = [];
            $timeforlesson = 0;
            if (!$grades = $DB->get_records('lesson_grades', ['lessonid' => $lessonsid, 'userid' => $userid], 'completed')) {
                $grades = [];
            }
            if (!$times = $DB->get_records('lesson_timer', ['lessonid' => $lessonsid, 'userid' => $userid], 'starttime')) {
                $times = [];
            }
            $attempts = $DB->get_recordset('lesson_attempts', ['lessonid' => $lessonsid, 'userid' => $userid], 'timeseen');
            foreach ($attempts as $attempt) {
                if (!isset($studentdata[$attempt->retry])) {
                    $n = 0;
                    $timestart = 0;
                    $timeend = 0;
                    $usergrade = null;
                    $eol = 0;
                    foreach($grades as $grade) {
                        if ($n === (int)$attempt->retry) {
                            $usergrade = round($grade->grade, 2);
                            break;
                        }
                        $n++;
                    }
                    $n = 0;
                    foreach($times as $time) {
                        if ($n === (int)$attempt->retry) {
                            if ($time->starttime >= $mintime && $time->starttime <= $maxtime) {
                                $timeend = $time->lessontime;
                                $timestart = $time->starttime;
                                $eol = $time->completed;
                                break;
                            }
                        }
                        $n++;
                    }
                    $studentdata[$attempt->retry] = [
                        "timestart" => $timestart,
                        "timeend" => $timeend,
                        "grade" => $usergrade,
                        "end" => $eol,
                        "try" => $attempt->retry,
                        "userid" => $attempt->userid,
                        "lessonid" => $lessonsid
                    ];
                }
            }
            $attempts->close();
            $branches = $DB->get_recordset('lesson_branch', ['lessonid' => $lessonsid, 'userid' => $userid], 'timeseen');
            foreach ($branches as $branch) {
                if (!isset($studentdata[$branch->retry])) {
                    $n = 0;
                    $timestart = 0;
                    $timeend = 0;
                    $usergrade = null;
                    $eol = 0;
                    foreach($times as $time) {
                        if ($n === (int)$branch->retry) {
                            if ($time->starttime >= $mintime && $time->starttime <= $maxtime) {
                                $timeend = $time->lessontime;
                                $timestart = $time->starttime;
                                $eol = $time->completed;
                                break;
                            }
                        }
                        $n++;
                    }
                    $studentdata[$branch->retry] = [
                        "timestart" => $timestart,
                        "timeend" => $timeend,
                        "grade" => $usergrade,
                        "end" => $eol,
                        "try" => $branch->retry,
                        "userid" => $branch->userid,
                        "lessonid" => $lessonsid
                    ];
                }
            }
            $branches->close();

            foreach ($times as $time) {
                $endoflesson = $time->completed;
                $foundmatch = false;
                foreach ($studentdata as $value) {
                    if ((int)$value['timestart'] === (int)$time->starttime) {
                        $foundmatch = true;
                        break;
                    }
                }
                $n = count($studentdata) + 1;
                if (!$foundmatch) {
                    $studentdata[] = [
                        "timestart" => $time->starttime,
                        "timeend" => $time->lessontime,
                        "grade" => null,
                        "end" => $endoflesson,
                        "try" => $n,
                        "userid" => $time->userid,
                        "lessonid" => $lessonsid
                    ];
                }
                if (empty($studentdata)) {
                    $studentdata[] = [
                        "timestart" => $time->starttime,
                        "timeend" => $time->lessontime,
                        "grade" => null,
                        "end" => $endoflesson,
                        "try" => 0,
                        "userid" => $time->userid
                    ];
                }
            }
            foreach ($studentdata as $try) {
                if ($try["grade"] !== null && $try["timestart"] >= $mintime && $try["timestart"] <= $maxtime) {
                    $timeforlesson = $try["timeend"] - $try["timestart"];
                } else if ($try["timestart"] >= $mintime && $try["timestart"] <= $maxtime) {
                    if ($try["end"]) {
                        $timeforlesson += $try["timeend"] - $try["timestart"];
                    } else {
                        $timeforlesson = 0;
                    }
                }
            }
            $totallessonstime += $timeforlesson;
        }
        return $totallessonstime;
    }

    /**
     * @param int $userid
     * @return int
     * @throws JsonException
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function calculate_user_bbb_totaltimes(int $userid): int {
        if (empty($this->bbbdata)) {
            $this->preparebbbdata();
        }
        $username = core_user::get_user($userid, 'firstname,lastname');
        $userfullname = $username->firstname . ' ' . $username->lastname;
        $usertotaltime = 0;
        foreach ($this->bbbdata as $cmbbbdata) {
            foreach ($cmbbbdata as $recordingbbbcmdata) {
                foreach ($recordingbbbcmdata as $userbbbcmdata) {
                    $userfullnamebbb = is_array($userbbbcmdata) ? $userbbbcmdata['userfullname'] : $userbbbcmdata->userfullname;
                    if (str_contains(
                        str_replace(' ', '', strtolower($userfullnamebbb)),
                        str_replace(' ', '', strtolower($userfullname)))
                    ) {
                        $str_time = preg_replace("/^(\d{1,2}):(\d{2})$/", "00:$1:$2", $userbbbcmdata->duration);
                        sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
                        if (isset($hours, $minutes, $seconds)) {
                            $time = $hours * 3600 + $minutes * 60 + $seconds;
                            if (is_int($time)) {
                                $usertotaltime += $time;
                            }
                        }
                    }
                }
            }
        }
        return $usertotaltime;
    }

    /**
     * @param int $bbbid
     * @param int $userid
     * @return int[]
     * @throws JsonException
     * @throws coding_exception
     * @throws dml_exception
     */
    public function calculate_user_bbb_forcm(int $bbbid, int $userid): array {
        if (empty($this->bbbdata)) {
            $this->preparebbbdata([$bbbid]);
        }
        $username = core_user::get_user($userid, 'firstname,lastname');
        $userfullname = $username->firstname . ' ' . $username->lastname;
        $usercmtime = 0;
        $numrecordings = 0;
        foreach ($this->bbbdata as $key => $cmbbbdata) {
            if ((int)$key === $bbbid) {
                foreach ($cmbbbdata as $recordingbbbcmdata) {
                    foreach ($recordingbbbcmdata as $userbbbcmdata) {
                        if (str_contains(
                            str_replace(' ', '', strtolower($userbbbcmdata->userfullname)),
                            str_replace(' ', '', strtolower($userfullname)))
                        ) {
                            $str_time = preg_replace("/^(\d{1,2}):(\d{2})$/", "00:$1:$2", $userbbbcmdata->duration);
                            sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
                            if (isset($hours, $minutes, $seconds)) {
                                $time = $hours * 3600 + $minutes * 60 + $seconds;
                                if (is_int($time)) {
                                    $usercmtime += $time;
                                    $numrecordings++;
                                }
                            }
                        }
                    }
                }
            }
        }
        return ['usercmtime' => $usercmtime, 'numrecordings' => $numrecordings];
    }

    /**
     * @return void
     * @throws JsonException
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     * @throws Exception
     */
    private function preparebbbdata() {
        global $DB;
        $cache = cache::make('report_fundae', 'bbbcourse');
        $key = 'fundae_' . $this->courseid;
        $bbbjson = $cache->get($key);
        $rebuild = false;
        if ($bbbjson !== false) {
            $data = (array)json_decode($bbbjson);
            if (!isset($data['timecreated']) || $data['timecreated'] <= strtotime("-12 hours")) {
                $rebuild = true;
            }
        }
        if ($rebuild === false && $bbbjson !== false) {
            $this->bbbdata = $data;
        } else {
            $fastmodinfo = get_fast_modinfo($this->courseid);
            $cms = $fastmodinfo ? $fastmodinfo->get_cms() : [];
            $cmbbb = array_filter($cms, static function($cm) {
                return $cm->modname === 'bigbluebuttonbn';
            });
            $bbbids = array_column($cmbbb, 'instance');
            $serverurl = trim(config::get('server_url')) . 'api/';
            $secretkey = config::get('shared_secret');
            $algorithm = config::get('checksum_algorithm');
            $action = 'getRecordings';
            foreach ($bbbids as $bbid) {
                $meetingid = $DB->get_record('bigbluebuttonbn', ['id' => $bbid, 'course' => $this->courseid], 'meetingid');
                if ($meetingid === false) {
                    continue;
                }
                $recordings = $DB->get_records('bigbluebuttonbn_recordings', ['bigbluebuttonbnid' => $bbid, 'courseid' => $this->courseid, 'status' => 2], '', 'id,recordingid');
                foreach ($recordings as $recording) {
                    $paramsstr = 'recordID=' . $recording->recordingid;
                    $checksum = hash($algorithm, $action . $paramsstr . trim($secretkey));
                    $params = [
                        'recordID' => $recording->recordingid,
                        'checksum' => $checksum
                    ];
                    $urldata = $serverurl . $action;
                    $curl = new curl();
                    $firstcurl = $curl->get($urldata, $params);
                    if (isset($firstcurl)) {
                        $XML = new SimpleXMLElement($firstcurl);
                        $playback = $XML->recordings->recording->playback;
                        if ($playback->format[1]->type = 'statistics') {
                            $statisticsurl = $playback->format[1]->url;
                            $htmldata = $curl->get($statisticsurl);
                            if (isset($htmldata)) {
                                $dom = new DOMDocument();
                                $dom->loadHTML($htmldata);
                                $overviewtable = $dom->getElementById('overview-table');
                                if ($overviewtable !== null) {
                                    foreach($overviewtable->getElementsByTagName('tr') as $tr) {
                                        $tds = $tr->getElementsByTagName('td');
                                        if ($tds->length === 5) {
                                            $this->bbbdata[$bbid][$recording->recordingid][] = [
                                                'userfullname' => mb_convert_encoding(substr(preg_replace(['/\s+/','/^\s|\s$/'],[' ',''], $tds->item(0)->nodeValue), 3), 'UTF-8', 'UTF-8'),
                                                'duration' => $tds->item(1)->nodeValue,
                                                'timejoined' => $tds->item(2)->nodeValue,
                                                'timeleft' => $tds->item(3)->nodeValue,
                                                'activityscore' => $tds->item(4)->nodeValue,
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($this->bbbdata)) {
                $this->bbbdata['timecreated'] = time();
                $cache->set($key, json_encode($this->bbbdata, JSON_THROW_ON_ERROR));
            }
        }
    }

    /**
     * @param int $courseid
     * @return array
     * @throws dml_exception
     */
    public static function get_enrolled_users_by_course(int $courseid): array {
        global $DB;
        $coursecontext = context_course::instance($courseid);
        $roles = get_roles_used_in_context($coursecontext);
        $studentroles = [];
        $teacherroles = [];
        $teachers = [];
        $students = [];
        foreach ($roles as $role) {
            $roledata = $DB->get_record('role', ['id' => $role->id], 'archetype');
            switch ($roledata->archetype) {
                case 'manager':
                case 'coursecreator':
                case 'editingteacher':
                case 'teacher':
                    $teacherroles[] = $role;
                    break;
                case 'student':
                case 'guest':
                case 'user':
                    $studentroles[] = $role;
                    break;
                default:
                    break;
            }
        }
        foreach ($teacherroles as $teacherrole) {
            $teachers[] = get_users_from_role_on_context($teacherrole, $coursecontext);
        }
        foreach ($studentroles as $studentrole) {
            $students[] = get_users_from_role_on_context($studentrole, $coursecontext);
        }
        $teachers = array_unique($teachers, SORT_REGULAR);
        $students = array_unique($students, SORT_REGULAR);
        $teachersids = [];
        foreach ($teachers as $teachersdata) {
            $teachersids += array_map(static function($teacher) {
                return $teacher->{'userid'};
            }, $teachersdata);
        }
        $studentsids = [];
        foreach ($students as $studentsdata) {
            $studentsids += array_map(static function($student) {
                return $student->{'userid'};
            }, $studentsdata);
        }
        return [array_values($teachersids), array_values($studentsids)];
    }
}
