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
 * @package report_fundae
 * @author 3iPunt <https://www.tresipunt.com/>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 3iPunt <https://www.tresipunt.com/>
 */

namespace report_fundae;

class filesrepository {
    /**
     * @param $files
     * @param $masterCSVFile
     */
    public static function join_files ($files, $masterCSVFile):void {
        $first = true;
        foreach ($files as $file) {
            $data = [];
            if (($handle = fopen($file->copy_content_to_temp(), "r")) !== false) {
                while (($dataValue = fgetcsv($handle, 1000)) !== false) {
                    $data[] = $dataValue;
                }
            }
            fclose($handle);
            if (!$first) {
                unset($data[0]);
            }
            $first = false;
            if (count($data) > 0) {
                foreach ($data as $value) {
                    try {
                        fputcsv($masterCSVFile, $value, ',');
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }
        fclose($masterCSVFile);
    }

    /**
     * @param $files
     * @param $params
     * @param $connection
     * @param false $sftp
     * @throws \coding_exception
     */
    public static function upload_files_to_server($files, $params, $connection, $sftp = false): void {
        $coursefiles = array();
        $activitiesfiles = array();
        $completionsfiles = array();
        foreach ($files as $file) {
            if (strpos($file->get_filename(), 'fundae_course_report') !== false) {
                $coursefiles[] = $file;
            } else if (strpos($file->get_filename(), 'fundae_activities_report') !== false) {
                $activitiesfiles[] = $file;
            } else if (strpos($file->get_filename(), 'fundae_completions_report') !== false) {
                $completionsfiles[] = $file;
            }
        }
        global $CFG;
        $remotepath = (substr($params['ftpremotepath'], -1) !== '/') ? $params['ftpremotepath'] . '/' : $params['ftpremotepath'];
        $coursepath = $CFG->dataroot . '/temp/report_fundae/reports/Elearning_status_course.csv';
        $activitiespath = $CFG->dataroot . '/temp/report_fundae/reports/Elearning_analytic_web.csv';
        $completionspath = $CFG->dataroot . '/temp/report_fundae/reports/Elearning_completions_course.csv';
        $mastercourseCSVFile = fopen($coursepath, 'wb+');
        $masteractivitiesCSVFile = fopen($activitiespath, 'wb+');
        $mastercompletionCSVFile = fopen($completionspath, 'wb+');
        self::join_files($coursefiles, $mastercourseCSVFile);
        self::join_files($activitiesfiles, $masteractivitiesCSVFile);
        self::join_files($completionsfiles, $mastercompletionCSVFile);
        if (!$sftp) {
            $localpath = $remotepath . 'Elearning_status_course.csv';
            ftp_put($connection, $remotepath, $localpath, FTP_BINARY);
            $localpath = $remotepath . 'Elearning_analytic_web.csv';
            ftp_put($connection, $remotepath, $localpath, FTP_BINARY);
            $localpath = $remotepath . 'Elearning_completions_course.csv';
            ftp_put($connection, $remotepath, $localpath, FTP_BINARY);
        } else {
            $ssh3sftp = ssh2_sftp($connection);
            $filename = 'Elearning_status_course.csv';
            $contentscourselocal = file_get_contents($CFG->dataroot . '/temp/report_fundae/reports/Elearning_status_course.csv');
            $uploaded = file_put_contents("ssh2.sftp://{$ssh3sftp}/{$remotepath}{$filename}", $contentscourselocal);
            if ($uploaded !== false) {
                mtrace(get_string('ftpuploaded', 'report_fundae', 'Elearning_status_course.csv'));
            } else {
                mtrace(get_string('ftpnotuploaded', 'report_fundae', 'Elearning_status_course.csv'));
            }
            $filename = 'Elearning_analytic_web.csv';
            $contentsactivitieslocal = file_get_contents($CFG->dataroot . '/temp/report_fundae/reports/Elearning_analytic_web.csv');
            $uploaded = file_put_contents("ssh2.sftp://{$ssh3sftp}/{$remotepath}{$filename}", $contentsactivitieslocal);
            if ($uploaded !== false) {
                mtrace(get_string('ftpuploaded', 'report_fundae', 'Elearning_analytic_web.csv'));
            } else {
                mtrace(get_string('ftpnotuploaded', 'report_fundae', 'Elearning_analytic_web.csv'));
            }
            $filename = 'Elearning_completions_course.csv';
            $contentscompletionlocal = file_get_contents($CFG->dataroot . '/temp/report_fundae/reports/Elearning_completions_course.csv');
            $uploaded = file_put_contents("ssh2.sftp://{$ssh3sftp}/{$remotepath}{$filename}", $contentscompletionlocal);
            if ($uploaded !== false) {
                mtrace(get_string('ftpuploaded', 'report_fundae', 'Elearning_completions_course.csv'));
            } else {
                mtrace(get_string('ftpnotuploaded', 'report_fundae', 'Elearning_completions_course.csv'));
            }
        }
    }

    /**
     * @param array $params
     * @param array $files
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function test_and_upload_ftp(array $params, array $files): bool {
        mtrace(get_string('ftpupload', 'report_fundae'));
        $ftpextension = extension_loaded ('ftp');
        $sftpextension = extension_loaded ('ssh2');
        if ($ftpextension || $sftpextension) {
            switch (get_config('report_fundae', 'typehost')) {
                case 0: // SFTP
                    if ($sftpextension && $sftpconnection = ssh2_connect($params['ftphost'], $params['ftpport'])) {
                        if ($login = @ssh2_auth_password($sftpconnection, $params['ftpuser'], $params['ftppassword'])) {
                            mtrace(get_string('ftpconexion', 'report_fundae'));
                            mtrace(get_string('ftpuploading', 'report_fundae', $params['ftpremotepath']));
                            self::upload_files_to_server($files, $params, $sftpconnection, true);
                            return true;
                        }
                        mtrace(get_string('ftploginerror', 'report_fundae'));
                        return false;
                    }
                    mtrace(get_string('ftpconnectionerror', 'report_fundae'));
                    return false;
                    break;
                case 1: // FTP
                    if ($ftpextension && $connection = ftp_connect($params['ftphost'], $params['ftpport'])) {
                        if ($login = ftp_login($connection, $params['ftpuser'], $params['ftppassword'])) {
                            mtrace(get_string('ftpconexion', 'report_fundae'));
                            mtrace(get_string('ftpuploading', 'report_fundae', $params['ftpremotepath']));
                            self::upload_files_to_server($files, $params, $connection);
                            return true;
                        }
                        mtrace(get_string('ftploginerror', 'report_fundae'));
                        ftp_close($connection);
                        return false;
                    }
                    mtrace(get_string('ftpconnectionerror', 'report_fundae'));
                    return false;
                    break;
                case 2: // Google Drive
                    self::upload_files_to_gdrive($files);
                    return true;
            }
        }
        mtrace(get_string('anyextensionsupload', 'report_fundae'));
        return false;
    }

    /**
     * @param $files
     * @throws \Google_Exception
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function upload_files_to_gdrive ($files) {
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');
        require_once($CFG->libdir . '/google/src/Google/autoload.php');
        require_once($CFG->libdir . '/google/lib.php');
        require_once($CFG->libdir . '/google/src/Google/Service/Drive.php');
        $clientid = get_config('report_fundae', 'clientid');
        $secret = get_config('report_fundae', 'clientsecret');
        $json = get_config('report_fundae', 'credentialsjson');
        $folderid = get_config('report_fundae', 'folderid');
        $historicfolderid = get_config('report_fundae', 'historicfolderid');
        if ($json === '' || $secret === '' || $clientid === '' || $folderid === '') {
            mtrace(get_string('missinggdrive', 'report_fundae'));
        } else {
            $client = new \Google_Client();
            $client->setScopes([\Google_Service_Drive::DRIVE]);
            $client->setAuthConfig($json);
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');
            session_start();
            $tokenPath = 'token.json';
            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($accessToken);
            }
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->refreshToken($client->getRefreshToken());
                } else {
                    $authUrl = $client->createAuthUrl();
                    printf("Open the following link in your browser:\n%s\n", $authUrl);
                    print 'Enter verification code: ';
                    $authCode = trim(fgets(STDIN));
                    $accessToken = $client->authenticate($authCode);
                    $client->setAccessToken($accessToken);
                    if (array_key_exists('error', $accessToken)) {
                        throw new Exception(join(', ', $accessToken));
                    }
                }
                if (!file_exists(dirname($tokenPath))) {
                    mkdir(dirname($tokenPath), 0700, true);
                }
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }
            mtrace(get_string('conexiongdrive', 'report_fundae'));
            $service = new Google_Service_Drive($client);
            $optParams = array(
                'q' => "mimeType = 'application/vnd.google-apps.folder'"
            );
            $folders = $service->files->listFiles($optParams);
            if (count($folders->getItems()) === 0) {
                mtrace(get_string('folderdnotfound', 'report_fundae'));
            } else {
                foreach ($folders->getItems() as $folder) {
                    if ($folderid === $folder->getId()) {
                        mtrace(get_string('folderdfound', 'report_fundae') . $folder->getTitle() . ' - ' . $folder->getId());
                        /** @var \Google_Service_Drive_DriveFile $foldercontent */
                        $foldercontent = $folder;
                    }
                    if ($historicfolderid === $folder->getId()) {
                        mtrace(get_string('folderdfound', 'report_fundae') . $folder->getTitle() . ' - ' . $folder->getId());
                        /** @var \Google_Service_Drive_DriveFile $historicfoldercontent */
                        $historicfoldercontent = $folder;
                    }
                }
                $todaydate = date('j-n-Y');
                if (isset($foldercontent)) {
                    $coursefiles = [];
                    foreach ($files as $file) {
                        if (strpos($file->get_filename(), 'fundae_course_report') !== false) {
                            $coursefiles[] = $file;
                        }
                    }
                    $children = $service->children->listChildren($foldercontent->getId());
                    foreach ($children->getItems() as $child) {
                        $childdata = $service->files->get($child->getId());
                        $filettitle = $childdata->getTitle();
                        if ($filettitle === 'Elearning_status_course.csv') {
                            mtrace(get_string('deleteoldfile', 'report_fundae', $filettitle));
                            $service->files->delete($child->getId());
                        }
                    }
                    $coursepath = $CFG->dataroot . '/temp/report_fundae/reports/Elearning_status_course.csv';
                    $mastercourseCSVFile = fopen($coursepath, 'wb+');
                    self::join_files($coursefiles, $mastercourseCSVFile);
                    $data = file_get_contents($coursepath);
                    try {
                        $fileMetadata = new \Google_Service_Drive_DriveFile();
                        $fileMetadata->setTitle(basename($coursepath));
                        $fileMetadata->setMimeType('text/csv');
                        $parent = new \Google_Service_Drive_ParentReference();
                        $parent->setId($foldercontent->getId());
                        $fileMetadata->setParents([$parent]);
                        $service->files->insert(
                            $fileMetadata, [
                                'data' => $data,
                                'mimeType' => 'text/csv',
                                'uploadType' => 'multipart'
                            ]
                        );
                        mtrace(get_string('createfile', 'report_fundae', basename($coursepath)));
                    } catch (\Exception $e) {
                        mtrace(print_object($e->getCode()));
                    }
                    if (isset($historicfoldercontent)) {
                        $historicchildren = $service->children->listChildren($historicfoldercontent->getId());
                        foreach ($historicchildren->getItems() as $child) {
                            $childdata = $service->files->get($child->getId());
                            $filettitle = $childdata->getTitle();
                            if ($filettitle === $todaydate  . ' ' . 'Elearning_status_course.csv') {
                                mtrace(get_string('deleteoldfile', 'report_fundae', $filettitle));
                                $service->files->delete($child->getId());
                            }
                        }
                        try {
                            $fileMetadata = new \Google_Service_Drive_DriveFile();
                            $fileMetadata->setTitle($todaydate . ' ' . basename($coursepath));
                            $fileMetadata->setMimeType('text/csv');
                            $parent = new \Google_Service_Drive_ParentReference();
                            $parent->setId($historicfoldercontent->getId());
                            $fileMetadata->setParents([$parent]);
                            $service->files->insert(
                                $fileMetadata, [
                                    'data' => $data,
                                    'mimeType' => 'text/csv',
                                    'uploadType' => 'multipart'
                                ]
                            );
                            mtrace(get_string('createhistoricfile', 'report_fundae', $todaydate . ' ' . basename($coursepath)));
                        } catch (\Exception $e) {
                            mtrace(print_object($e->getCode()));
                        }
                    }
                } else {
                    mtrace(get_string('folderdnotfound', 'report_fundae'));
                }
            }
        }
    }
}
