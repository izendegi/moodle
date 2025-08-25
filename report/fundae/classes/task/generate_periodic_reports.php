<?php

namespace report_fundae\task;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\Common\Creator\WriterFactory;
use coding_exception;
use context_course;
use context_system;
use core\task\scheduled_task;
use core_text;
use dml_exception;
use Exception;
use moodle_exception;
use report_fundae\activitiesreportapi;
use report_fundae\coursereportapi;
use report_fundae\filesrepository;
use stdClass;
use stored_file;

defined('MOODLE_INTERNAL') || die();

class generate_periodic_reports extends scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_name(): string {
        return get_string('reportgeneration', 'report_fundae');
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function execute(): void {
        mtrace("FUNDAE report START");
        $periodicgenerationenabled = (bool) get_config('report_fundae', 'enablescheduledreportgeneration');
        if (!$periodicgenerationenabled) {
            mtrace('Scheduled reports generation disabled.');
            mtrace("FUNDAE report END");
            return;
        }
        $this->generate_scheduled_reports();

        mtrace("FUNDAE report END");
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function generate_scheduled_reports(): void {
        $generatedfiles = [];
        $bulkmode = (int) get_config('report_fundae', 'bulkscheduledreportgenerationmode');
        if (1 === $bulkmode) { // All courses reports.
            $generatedfiles = array_merge($generatedfiles, $this->generate_all_courses_reports());
        } else if (2 === $bulkmode) { // All courses and activities reports.
            $generatedfiles = array_merge($generatedfiles, $this->generate_all_courses_reports());
            $generatedfiles = array_merge($generatedfiles, $this->generate_all_activities_reports());
        }

        // Mail generated reports within a zip file if mailing options enabled.
        if (empty($generatedfiles)) {
            mtrace("FUNDAE report no files generated");
            return;
        }

        // FTP
        $params = array(
            'ftphost' => (string)get_config('report_fundae', 'ftphost'),
            'ftpport' => (int)get_config('report_fundae', 'ftpport'),
            'ftpuser' => (string)get_config('report_fundae', 'ftpuser'),
            'ftppassword' => (string)get_config('report_fundae', 'ftppassword'),
            'ftpremotepath' => (string)get_config('report_fundae', 'ftpremotepath')
        );
        if (count(array_filter($params)) === count($params)) {
            filesrepository::test_and_upload_ftp($params, $generatedfiles);
        } else {
            mtrace(get_string('ftpsettingerror', 'report_fundae'));
        }

        $mailing = (string) get_config('report_fundae', 'scheduledreportmailing');
        if (empty($mailing)) {
            mtrace("FUNDAE report no mailing");
            return;
        }
        $recipients = explode(',', $mailing);
        $recipients = array_filter($recipients, 'validate_email');
        if (empty($recipients)) {
            mtrace("FUNDAE report no recipients");
            return;
        }
        $zipname = 'scheduled_reports_' . date('YmdHis') . '.zip';
        $zipfile = $this->generate_temp_zip_for_stored_files($generatedfiles);
        foreach ($recipients as $recipient) {
            $subject = get_string('scheduledreportssubject', 'report_fundae', date('Ymd'));
            $messagehtml = get_string('scheduledreportsmessage', 'report_fundae');
            $this->send_local_file_by_email($recipient, $subject, $messagehtml, $zipname, $zipfile);
        }
        @unlink($zipfile);
    }

    /**
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function generate_all_activities_reports() {
        $courses = get_courses();
        $writertype = (string) get_config('report_fundae', 'scheduledreportgenerationformat');
        $generatedfiles = [];
        foreach ($courses as $course) {
            if ((int)$course->id === 1) {
                continue;
            }
            $courseid = $course->id;
            $userenrolments = enrol_get_course_users($courseid, true);
            foreach ($userenrolments as $userenrolment) {
                $userid = $userenrolment->id;
                $generated = $this->generate_activity_report($courseid, $userid, $writertype);
                if ($generated) {
                    $generatedfiles[] = $generated;
                }
            }

        }
        return $generatedfiles;
    }

    /**
     * @param $courseid
     * @param $userid
     * @param $writertype
     * @return false|stored_file
     * @throws moodle_exception
     * @throws coding_exception
     * @throws dml_exception
     */
    private function generate_activity_report($courseid, $userid, $writertype) {
        $headers = activitiesreportapi::get_headers();
        $userdata = activitiesreportapi::get_data($courseid, $userid);
        $filename = self::generate_activity_report_name($courseid, $userid, $writertype);
        $filerecord = self::get_new_report_filerecord($filename);

        return self::generate_report($writertype, $headers, $userdata, $filerecord);
    }

    /**
     * @param int $courseid
     * @param int $userid
     * @param string $extension
     * @return string
     * @throws dml_exception
     */
    private static function generate_activity_report_name(int $courseid, int $userid, string $extension): string {
        $prefix = 'report_fundae_activities_report';
        $date = date('YmdHis');
        $context = context_course::instance($courseid);
        $coursename = get_course($courseid)->shortname;
        $coursename = format_string($coursename, true, ['context' => $context]);
        $coursename = str_replace(' ', '_', $coursename);
        $coursename = core_text::strtolower(trim(clean_filename($coursename), '_'));
        $username = fullname(\core_user::get_user($userid, '*', MUST_EXIST));
        $username = str_replace(' ', '_', $username);
        $username = core_text::strtolower(trim(clean_filename($username), '_'));
        $hash = hash('crc32', time());

        return $prefix . '-' . $coursename . '-' . $username . '-' . $date . '-' . $hash . '.' . $extension;
    }

    /**
     * @param stored_file[] $generatedfiles
     * @return string Local path of temporary zip
     */
    private function generate_temp_zip_for_stored_files(array $generatedfiles): string {
        global $CFG;
        $filelist = [];
        foreach ($generatedfiles as $generatedfile) {
            $filelist[$generatedfile->get_filename()] = $generatedfile;
        }

        $tempzip = tempnam($CFG->tempdir . '/', 'scheduled_reports');
        $zipper = new \zip_packer();
        $zipper->archive_to_pathname($filelist, $tempzip);

        return $tempzip;
    }

    /**
     * @param string $recipient
     * @param string $subject
     * @param string $messagehtml
     * @param string $filename
     * @param string $localpath
     * @return bool
     */
    private function send_local_file_by_email(string $recipient, string $subject, string $messagehtml,
                                                    string $filename, string $localpath): bool {
        $recipientuser = $this->get_dummy_user_record_with_email($recipient);
        $noreplyuser = \core_user::get_noreply_user();
        $messagetext = html_to_text($messagehtml);

        return email_to_user($recipientuser, $noreplyuser, $subject, $messagetext, $messagehtml, $localpath, $filename);
    }

    /**
     * Helper function to return a dummy user record with a given email.
     *
     * @param string $email
     * @return stdClass
     */
    private function get_dummy_user_record_with_email($email): stdClass {
        return (object) [
            'email' => $email,
            'mailformat' => FORMAT_HTML,
            'id' => -30,
            'firstname' => '',
            'username' => '',
            'lastname' => '',
            'confirmed' => 1,
            'suspended' => 0,
            'deleted' => 0,
            'picture' => 0,
            'auth' => 'manual',
            'firstnamephonetic' => '',
            'lastnamephonetic' => '',
            'middlename' => '',
            'alternatename' => '',
            'imagealt' => '',
        ];
    }

    /**
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function generate_all_courses_reports(): array {
        $courses = get_courses();
        $writertype = (string) get_config('report_fundae', 'scheduledreportgenerationformat');
        $generatedfiles = [];
        foreach ($courses as $course) {
            if ((int)$course->id === 1) {
                continue;
            }
            $courseid = $course->id;
            $generated = self::generate_course_report($courseid, $writertype);
            if ($generated) {
                $generatedfiles[] = $generated;
            }
        }
        return $generatedfiles;
    }

    /**
     * @param int $courseid
     * @param string $writertype
     * @return false|stored_file
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private static function generate_course_report(int $courseid, string $writertype) {
        $headers = coursereportapi::get_headers();
        $userdata = coursereportapi::get_data($courseid);
        $filename = self::generate_course_report_name($courseid, $writertype);
        $filerecord = self::get_new_report_filerecord($filename);

        return self::generate_report($writertype, $headers, $userdata, $filerecord);
    }

    /**
     * @param int $courseid
     * @param string $extension
     * @return string
     * @throws dml_exception
     */
    private static function generate_course_report_name(int $courseid, string $extension): string {
        $prefix = 'report_fundae_course_report';
        $context = context_course::instance($courseid);
        $coursename = get_course($courseid)->shortname;
        $coursename = format_string($coursename, true, ['context' => $context]);
        $coursename = str_replace(' ', '_', $coursename);
        $coursename = core_text::strtolower(trim(clean_filename($coursename), '_'));
        return $prefix . '-' . $coursename . '-' . time() . '.' . $extension;
    }

    /**
     * @param string $filename
     * @return array
     * @throws dml_exception
     */
    private static function get_new_report_filerecord(string $filename): array {
        return [
            'contextid' => context_system::instance()->id,
            'component' => 'report_fundae',
            'filearea' => 'reports',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $filename,
        ];
    }

    /**
     * @param string $writertype
     * @param array $headers
     * @param array $userdata
     * @param array $filerecord
     * @return false|stored_file
     */
    private static function generate_report(string $writertype, array $headers, array $userdata, array $filerecord) {
        try {
            // Prepare writer and temporary file.
            $writer = WriterFactory::createFromFile($writertype);
            $tmpdir = make_unique_writable_directory(make_temp_directory('report_fundae/reports'));
            $tmpfilepath = $tmpdir . '/reportfile.' . $writertype;
            $writer->openToFile($tmpfilepath);

            // Add headers.
            if (!empty($headers)) {
                $data = [];
                foreach ($headers as $header) {
                    $data[] = Cell::fromValue($header);
                }
                $row = new Row($data, null);
                $writer->addRow($row);
            }

            foreach ($userdata as $rowcolumns) {
                $row = new Row($rowcolumns, null);
                $writer->addRow($row);
            }

            // Close writer and save temporary file in Moodle file storage system.
            $writer->close();
            $fs = get_file_storage();
            $generatedfile = $fs->create_file_from_pathname($filerecord, $tmpfilepath);
        } catch (Exception $ex) {
            // Try to clean temporary files and folders that may have been generated before the exception occurred.
            if (!empty($tmpfilepath)) {
                @unlink($tmpfilepath);
            }
            if (!empty($tmpdir)) {
                remove_dir($tmpdir);
            }
            return false;
        }
        // Remove temporary files and folders.
        @unlink($tmpfilepath);
        remove_dir($tmpdir);

        return $generatedfile;
    }

}
