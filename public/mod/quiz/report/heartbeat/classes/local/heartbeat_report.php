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

/**
 * This file defines the quiz_heartbeat report class.
 *
 * @package   quiz_heartbeat
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_heartbeat\local;

use core\dml\sql_join;

/**
 * Quiz report subclass for the quiz_heartbeat report.
 *
 * This report allows you to download text responses and file attachments submitted
 * by students as a response to quiz essay questions.
 *
 * @package   quiz_heartbeat
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class heartbeat_report extends \mod_quiz\local\reports\attempts_report {
    /** @var object course object */
    protected object $course;

    /** @var object course module object */
    protected object $cm;

    /** @var object quiz object */
    protected object $quiz;

    /** @var quiz_heartbeat_options options for the report */
    protected heartbeat_options $options;

    /** @var array attempt and user data */
    protected array $attempts;

    /** @var int id of the currently selected group */
    protected int $currentgroup;

    /**
     * Override the parent function, because we have some custom stuff to initialise.
     *
     * @param string $mode
     * @param string $formclass
     * @param stdClass $quiz
     * @param stdClass $cm
     * @param stdClass $course
     * @return array with four elements:
     *      0 => integer the current group id (0 for none).
     *      1 => \core\dml\sql_join Contains joins, wheres, params for all the students in this course.
     *      2 => \core\dml\sql_join Contains joins, wheres, params for all the students in the current group.
     *      3 => \core\dml\sql_join Contains joins, wheres, params for all the students to show in the report.
     *              Will be the same as either element 1 or 2.
     */
    public function init($mode, $formclass, $quiz, $cm, $course): array {
        global $DB;

        // First, we call the parent init function...
        [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins] =
            parent::init($mode, $formclass, $quiz, $cm, $course);

        $this->options = new heartbeat_options('heartbeat', $quiz, $cm, $course);

        $this->options->process_settings_from_params();
        if ($fromform = $this->form->get_data()) {
            $this->options->process_settings_from_form($fromform);
        }

        $this->form->set_data($this->options->get_initial_form_data());

        $this->course = $course;
        $this->cm = $cm;
        $this->quiz = $quiz;
        $this->currentgroup = $currentgroup;

        $this->hasgroupstudents = false;
        if (!empty($groupstudentjoins->joins)) {
            $sql = "SELECT DISTINCT u.id
                               FROM {user} u
                                    {$groupstudentjoins->joins}
                              WHERE {$groupstudentjoins->wheres}";
            $this->hasgroupstudents = $DB->record_exists_sql($sql, $groupstudentjoins->params);
        }

        $this->attempts = $this->get_pending_attempts($groupstudentjoins);

        return [$currentgroup, $allstudentjoins, $groupstudentjoins, $allowedjoins];
    }

    /**
     * Display the form or, if the "Download" button has been pressed, invoke
     * preparation and shipping of the ZIP archive.
     *
     * @param stdClass $quiz this quiz.
     * @param stdClass $cm the course-module for this quiz.
     * @param stdClass $course the coures we are in.
     */
    public function display($quiz, $cm, $course) {
        $this->init('heartbeat', 'quiz_heartbeat\form\heartbeat_form', $quiz, $cm, $course);

        $this->display_form();
        $this->display_table();
    }

    /**
     * Display the settings form with the download button. May display an error notification, e. g.
     * if there are no attempts or if we already know that there are no essay questions.
     *
     * @return void
     */
    protected function display_form(): void {
        // Printing the standard header. We'll set $hasquestions and $hasstudents to true here,
        // because we do not need a specific notification when there are no questions or no students.
        // The default message "Nothing to display" will be enough.
        $this->print_standard_header_and_messages(
            $this->cm,
            $this->course,
            $this->quiz,
            $this->options,
            $this->currentgroup,
            true,
            true,
        );

        $this->form->display();
    }

    /**
     * Fetch the relevant attempts as well as the name (firstname, lastname) of the user they belong to.
     *
     * @param sql_join $joins joins, wheres, params to select the relevant subset of attemps (all or selected group)
     * @return array
     */
    public function get_pending_attempts(sql_join $joins): array {
        global $DB;

        // If there are no WHERE clauses (i. e. because no group has been selected), we add a dummy
        // clause to simplify the syntax of the query.
        if (empty($joins->wheres)) {
            $joins->wheres = '1 = 1';
        }

        // Construct the sorting criterion. By default, we sort by the last name and first name.
        // For the elapsed time, we need to inverse the sort order, because the actual value from
        // the DB is the timestamp and smaller timestamps mean more time has elapsed.
        if ($this->options->tsort === 'time') {
            $sortdir = ($this->options->tdir == SORT_DESC ? 'ASC' : 'DESC');
        } else {
            $sortdir = ($this->options->tdir == SORT_ASC ? 'ASC' : 'DESC');
        }
        switch ($this->options->tsort) {
            case 'time':
                $sort = "timecreated $sortdir";
                break;
            case 'lastname':
                $sort = "u.lastname $sortdir, u.firstname $sortdir";
                break;
            case 'firstname':
                $sort = "u.firstname $sortdir, u.lastname $sortdir";
                break;
        }

        // Parameters for the SQL query will include the quiz' ID for sure.
        $params = ['iquizid' => $this->quiz->id, 'quizid' => $this->quiz->id];

        // The user may choose to filter the table by the initial of the first and/or last name.
        // We use 'AND' at the start of the condition, because we place it after other conditions.
        $nameconditions = '';
        if (!empty($this->options->tifirst) && preg_match('/[A-Z]/i', $this->options->tifirst)) {
            $nameconditions .= 'AND ' . $DB->sql_like('u.firstname', ':initialfirstname', false);
            $params = $params + ['initialfirstname' => $this->options->tifirst . '%'];
        }
        if (!empty($this->options->tilast) && preg_match('/[A-Z]/i', $this->options->tilast)) {
            $nameconditions .= 'AND ' . $DB->sql_like('u.lastname', ':initiallastname', false);
            $params = $params + ['initiallastname' => $this->options->tilast . '%'];
        }

        // In order for get_records_sql() to work properly, we need the first column of the result
        // set to be unique, so we add some random value.
        $dbrand = self::db_random();

        // We use a nested query. The inner query will fetch the most recent timestamp related to
        // the inprogress attempts at the given quiz for each student. The outer GROUP BY is needed, because
        // there might multiple question_attempt_steps with the exact same time stamp (e.g. at the start of
        // the attempt there will be one per question, or after an automatic save there would be one for
        // each modified question on the same page). Also, it is necessary to filter for the quiz and state again,
        // because -- by chance -- some student might have finished the same (or another) quiz at some later moment
        // which would then lead to wrong results.
        $sql = "SELECT $dbrand, MAX(st.sequencenumber) AS sequencenumber, MAX(st.timecreated) AS timecreated,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
                  FROM {question_attempt_steps} st
                  JOIN {user} u on u.id = st.userid
                  JOIN {question_attempts} qa on st.questionattemptid = qa.id
                  JOIN {quiz_attempts} a on a.uniqueid = qa.questionusageid
                       $joins->joins
                 WHERE $joins->wheres
                       $nameconditions
                       AND a.quiz = :quizid AND a.state = 'inprogress'
                       AND st.timecreated in (
                            SELECT MAX(ist.timecreated)
                              FROM {question_attempt_steps} ist
                              JOIN {question_attempts} iqa on ist.questionattemptid = iqa.id
                              JOIN {quiz_attempts} ia on ia.uniqueid = iqa.questionusageid
                             WHERE ia.quiz = :iquizid AND ia.state = 'inprogress'
                          GROUP BY ist.userid
                        )
              GROUP BY st.userid, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
              ORDER BY $sort";

        $results = $DB->get_records_sql($sql, $params + $joins->params);

        return $results;
    }

    /**
     * Build the report using a flexible_table.
     *
     * @return void
     */
    protected function display_table(): void {
        global $PAGE;

        // Setup the flexible_table according to our needs. It should be sortable, except for the
        // type column and we want to have the bars where the user can filter for the first letter
        // of the first and/or last name.
        $table = new \flexible_table('heartbeatoverview');
        $table->define_baseurl($PAGE->url);
        $table->initialbars(true);
        $table->set_attribute('id', 'heartbeatoverview');
        $table->define_columns(['fullname', 'time', 'type']);
        $table->define_headers([
            '',
            get_string('timeelapsed', 'quiz_heartbeat'),
            get_string('type', 'quiz_heartbeat'),
        ]);
        $table->sortable(true, 'lastname');
        $table->no_sorting('type');
        $table->setup();

        // Iterate over all attempts and prepare the table rows. We store the current time
        // here in order to use the same value for all attempts.
        $currenttime = time();
        foreach ($this->attempts as $attempt) {
            $secondselapsed = $currenttime - $attempt->timecreated;

            // By default, we assume it was a manual save. Automatic saves and start of attempt
            // have special values in the DB, i. e. negative sequence number for automatic saves
            // and 0 for the start of the attempt.
            $type = get_string('manualsave', 'quiz_heartbeat');
            if ($attempt->sequencenumber === '0') {
                $type = get_string('started', 'quiz_heartbeat');
            } else if ($attempt->sequencenumber[0] === '-') {
                $type = get_string('automaticsave', 'quiz_heartbeat');
            }

            $table->add_data_keyed([
                'fullname' => fullname($attempt),
                'time' => format_time($secondselapsed),
                'type' => $type,
            ]);
        }

        $table->finish_output();
    }

    /**
     * Return a command that can be used in an SQL query to generate a column with unique values.
     * The command depends on the DB engine in use.
     *
     * @return string
     */
    private static function db_random(): string {
        global $DB;

        switch ($DB->get_dbfamily()) {
            case 'mssql':
                return 'NEWID()';
            case 'mysql':
                return 'UUID_SHORT()';
            case 'postgres':
                return 'RANDOM()';
            case 'oracle':
                return 'dbms_random.value';
            default:
                return 'RAND()';
        }
    }
}
