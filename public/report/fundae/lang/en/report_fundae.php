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

$string['pluginname'] = 'Fundae Report';
$string['fundae:manage'] = 'Manage Fundae Reports';
$string['fundae:view'] = 'See Fundae Reports';

$string['usertime'] = 'User Times';
$string['timeoncourse'] = 'Time for course';
$string['timeactivities'] = 'Time in activities';
$string['timescorms'] = 'Time in SCORMs';
$string['timezoom'] = 'Time in Zoom';
$string['timebbb'] = 'Time in BBB';
$string['timescreen'] = 'Screen time';
$string['timeinteracting'] = 'Time in interacting';
$string['numberofsessions'] = 'Number of sessions';
$string['daysonline'] = 'Days online';
$string['ratioonline'] = 'Ratio connected';
$string['messages'] = 'Messages';
$string['messagestostudents'] = 'Messages to students';
$string['messagestoteachers'] = 'Messages to teachers';
$string['studentscontacted'] = 'Students contacted';
$string['teacherscontacted'] = 'Teachers contacted';
$string['access'] = 'Access to the course';
$string['firstaccess'] = 'First access';
$string['lastaccess'] = 'Last access';
$string['activityfirstaccess'] = 'Primer acceso a la actividad';
$string['activitylastaccess'] = 'Último acceso a la actividad';
$string['actions'] = 'Actions';
$string['details'] = 'User details';
$string['nodata'] = '-';
$string['activityname'] = 'Activity name';
$string['screentime'] = 'Screen Time';
$string['engagedtime'] = 'Engaged time';
$string['hits'] = 'Events count';
$string['unabletogeneratereports'] = 'Cron seems to be disabled. If cron is not running scheduled reports won\'t be generated. Contact your Moodle administrator for more information.';
$string['general'] = 'General';
$string['enableteachersseereports'] = 'Show reports to teachers';
$string['enableteachersseereports_desc'] = 'Teachers will be able to view and download their course reports, where they will be able to see the data of all students.';
$string['enablestudentsseereports'] = 'Students see reports';
$string['enablestudentsseereports_desc'] = 'Students will be able to view and download their own reports, where only their own data will be displayed.<br><b>Enabling or disabling this option will enable/disable the "moodle/site:viewreports" capability for students to view the reports tab in courses.</b>';
$string['enablebbbstatistics'] = 'Show Big Blue Button statistics';
$string['enablebbbstatistics_desc'] = '<b>Check this option if you have Big Blue Button configured on your platform and use it in your courses.</b> A new column will be displayed in the reports with the learners\' times in the bbb meetings, and the times will be added to the activity and course times.';
$string['timetracking'] = 'Time tracking';
$string['sessionthreshold'] = 'Session threshold (minutes)';
$string['sessionthreshold_desc'] = 'Each session is estimated by a set of two or more consecutive hits (relevant registered events) in which the elapsed time between any pair of consecutive hits does not overcome this amount of time. Notice that setting an unrealistically big threshold will most likely produce an overestimation of session times.';
$string['reportgeneration'] = 'Report generation';
$string['enablescheduledreportgeneration'] = 'Enable scheduled report generation';
$string['enablescheduledreportgeneration_desc'] = 'Enable or disable periodic report generation. The default schedule can be changed from Site administration > Server > Tasks > Scheduled tasks';
$string['bulkscheduledreportgenerationmode'] = 'Bulk scheduled reports mode';
$string['bulkscheduledreportgenerationmode_desc'] = 'This setting only applies if scheduled reports setting is enabled. If \'All courses\' is selected all courses report files will be generated during the scheduled process. If \'All courses and activities\' is selected all courses and activities report files will be generated during the scheduled process.';
$string['disabled'] = 'Disabled';
$string['allcourses'] = 'All courses';
$string['allcoursesandactivities'] = 'All courses and activities';
$string['scheduledreportmailing'] = 'Email scheduled reports';
$string['scheduledreportmailing_desc'] = 'Comma separated list of emails that will receive the selected or bulk scheduled report files each time they are generated.';
$string['scheduledreportgenerationformat'] = 'Scheduled reports format';
$string['scheduledreportgenerationformat_desc'] = 'Pick the file format for the periodically generated reports.';
$string['ftpnoextension'] = 'The php "ftp" extension required for uploading reports to the server with an FTP configuration has not been installed or enabled';
$string['sftpnoextension'] = 'The php "ssh2" extension required for uploading reports to the server with an SFTP configuration has not been installed or enabled';
$string['enableextensions'] = 'When you enable any of the PHP extensions required for uploading reports, the fields for connection to the server will be displayed';
$string['anyextensionsupload'] = 'No PHP extension is installed or enabled for file upload';
$string['ftpconfig'] = 'SFTP/FTP Config';
$string['ftpinformation'] = 'Uploading files to SFTP/FTP only applies if scheduled report settings are enabled. If a valid SFTP/FTP configuration is established, periodic report generation will upload the document to the specified path each time it is generated.';
$string['credentialsjson'] = 'Credentials Json';
$string['credentialsjson_help'] = 'Add the content of the file credentials.json.<br>Credentials is a JSON file that you get when you configure the connection API in Google, in the credentials panel, at https://console.developers.google.com/';
$string['typehost'] = 'Host type';
$string['typehost_help'] = 'The type of connection established will be used';
$string['clientid'] = 'Client ID';
$string['clientid_help'] = 'Corresponds to "client_id" in the json file';
$string['clientsecret'] = 'Client Secret';
$string['clientsecret_help'] = 'Corresponds to "client_secret" in the json file';
$string['folderid'] = 'Folder id';
$string['folderid_help'] = 'Corresponds to id of folder in google drive. Appear in url.';
$string['historicfolderid'] = 'Folder ID of the History';
$string['historicfolderid_help'] = 'It corresponds to the id of the folder where the file history is stored on the Google drive. It appears in the url.';
$string['ftphost'] = 'Host';
$string['ftpport'] = 'Port';
$string['ftpuser'] = 'Username';
$string['ftppassword'] = 'Password';
$string['ftpremotepath'] = 'Remote file path';
$string['ftphost_help'] = 'FTP host without protocol or port. Example: ftp.moodle.org';
$string['ftpport_help'] = 'FTP port. Example: 21';
$string['ftpuser_help'] = 'Valid FTP user.';
$string['ftppassword_help'] = 'Valid FTP password.';
$string['ftpremotepath_help'] = 'Remote path. Example: /home/user/uploads/';
$string['generateperiodicreports'] = 'Generate periodic reports';
$string['scheduledreportssubject'] = 'Scheduled reports {$a}';
$string['scheduledreportsmessage'] = '<p>Find attached the scheduled reports</p>';
$string['ftpsettingerror'] = 'The SFTP/FTP settings are not set or are invalid';
$string['ftpuploaded'] = '{$a} has been uploaded';
$string['ftpnotuploaded'] = 'Not able to upload {$a}';
$string['ftpconexion'] = 'Connection established';
$string['ftpuploading'] = 'Uploading files to {$a}';
$string['ftploginerror'] = 'Login error. Cannot log in the SFTP/FTP host with the given user and password.';
$string['ftpuploaderror'] = 'Upload error. An error happened when trying to upload the report file to the SFTP/FTP server.';
$string['ftpconnectionerror'] = 'Connection error. Cannot connect to the SFTP/FTP server.';
$string['missinggdrive'] = 'Missing configuration for uploading files to Google Drive';
$string['conexiongdrive'] = 'Connection established with Google Drive, searching on folders.';
$string['folderdfound'] = 'Folder not found: ';
$string['folderdnotfound'] = 'Not folders found.';
$string['deleteoldfile'] = 'Deleting the previous file: {$a}';
$string['createfile'] = 'New file created: {$a}';
$string['createhistoricfile'] = 'Historic file created: {$a}';
$string['ftpupload'] = 'Upload of files to the server:';
$string['coursecompletion'] = '% Completed';
$string['coursecompletion_help'] = 'The percentage of the course that this user has completed.

Take into account that some courses may have the completion tracking disabled even if the have activities..
';
$string['totalactivitiestimes'] = 'Time in activities';
$string['totalactivitiestimes_help'] = 'Dedication time attributable to course activities.';
$string['totalscormtime'] = 'Time in SCORMs';
$string['totalzoomtime'] = 'Time in Zoom';
$string['totalbbbtime'] = 'Time in BBB';
$string['totalscormtime_help'] = 'The sum of all the time tracked by this user in all the SCORMs within the course.';
$string['dedicationtime'] = 'Dedication time';
$string['dedicationtime_help'] = 'Dedication time is estimated based in the concepts of Session and Session duration applied to relevant log entries / hits.

* **Hit:** Every time that a user access to a page in Moodle a log entry is stored.
* **Session:** set of two or more consecutive hits in which the elapsed time between every pair of consecutive hits does not overcome an established maximum time (set to {$a} minutes).
* **Session duration:** elapsed time between the first and the last hit of a session.
* **Dedication time:** the sum of all session durations for a user.
';
$string['connectionratio'] = 'Connection ratio';
$string['connectionratio_help'] = '**Days connected / Duration of the course in days**

A value close to 1 means that the user connected almost every day since the course started.

A value close to 0 means that the user connected few days since the course started.
';
$string['activitiesreport'] = 'Activities Report';
$string['viewuser'] = 'View user';
$string['filteroptions'] = 'Filters';
$string['resultsperpage'] = 'Results per page';
$string['applyfilters'] = 'Apply filters';
$string['coursereport'] = 'Course Report';
$string['entitycoursecompletion'] = 'Course completion';
$string['profiledepartment'] = 'Profile department';
$string['entitycourseenrolment'] = 'Course enrolment';
$string['lastcourseaccess'] = 'Last course access';
$string['course_enrolment_timestarted'] = 'Enrolment started';
$string['course_enrolment_timeended'] = 'Enrolment ended';
$string['course_completion_days_course'] = 'Days taking course';
$string['course_completion_days_enrolled'] = 'Days enrolled';
$string['course_completion_progress'] = 'Progress';
$string['course_completion_progress_percent'] = 'Progress (%)';
$string['course_completion_reaggregate'] = 'Time reaggregated';
$string['course_completion_timecompleted'] = 'Time completed';
$string['course_completion_timeenrolled'] = 'Time enrolled';
$string['course_completion_timestarted'] = 'Time started';
$string['course_enrolment_status'] = 'Enrolment status';
$string['lessthanaday'] = 'Less than a day';
$string['urlgradebook'] = 'Grade Book';
$string['allusers'] = 'All users';
$string['selectuser'] = 'Select a user';