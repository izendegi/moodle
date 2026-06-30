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
 * Language strings for mod_typeform
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['activitymodaltitle'] = 'Typeform activity';
$string['clicktoreload'] = 'Recargar página';
$string['loadingforms'] = 'Loading forms...';
$string['modulename'] = 'Typeform Survey';
$string['modulename_help'] = 'The Typeform activity module allows you to embed Typeform surveys into your Moodle course. Students can complete surveys directly within Moodle, and completion is automatically tracked.';
$string['modulenameplural'] = 'Typeform Surveys';
$string['name'] = 'Name';
$string['name_help'] = 'Insert a name for the module activity';
$string['pluginadministration'] = 'Typeform administration';
$string['pluginname'] = 'Typeform';
$string['reloadpage'] = 'Para que vuelva a salir la modal recargue la página o pulse el siguiente enlace: ';

// Settings
$string['typeformsettings'] = 'Typeform Settings';
$string['selecttypeform'] = 'Select Typeform';
$string['selecttypeform_help'] = 'Select a Typeform survey from your Typeform account. Only forms from authorized workspaces will be shown.';
$string['includestudentcode'] = 'Include anonymous Student Code';
$string['includestudentcode_help'] = 'The student_code parameter is always anonymised (hashed) before being sent to Typeform. If enabled, this anonymised student_code is included in the form URL; if disabled, it is not sent at all. Disabled by default.';
$string['apitoken'] = 'Typeform API Token';
$string['apitoken_desc'] = 'Enter your Typeform API token. You can obtain this from your Typeform account settings.';
$string['allowedworkspaces'] = 'Allowed Workspaces';
$string['allowedworkspaces_desc'] = 'Comma-separated list of workspace IDs that are allowed to be used. Leave empty to allow all workspaces.';
$string['testconnection'] = 'Test Connection';
$string['notokenconfigured'] = 'Typeform API token is not configured. Please configure it in the plugin settings.';

// Errors
$string['errortypeformrequired'] = 'You must select a Typeform survey.';
$string['errorloadingforms'] = 'Error loading Typeform surveys. Please check your API token and try again.';
$string['notypeforms'] = 'No Typeform surveys found.';

// Completion
$string['completiondetail:submit'] = 'Student must submit the form';
$string['formcompleted'] = 'Thank you! Your response has been recorded.';
$string['formstarted'] = 'formstarted';
$string['alreadycompleted'] = 'You have already completed this survey.';

// Privacy
$string['privacy:metadata'] = 'The Typeform module does not store any personal data. Survey responses are stored in Typeform, not in Moodle. Moodle only stores completion status (whether the user completed the survey).';
$string['privacy:metadata:course_modules_completion'] = 'Information about completion of Typeform activities';
$string['privacy:metadata:course_modules_completion:userid'] = 'The user ID who completed the Typeform activity';
$string['privacy:metadata:course_modules_completion:completionstate'] = 'Whether the Typeform activity has been completed';
$string['privacy:metadata:course_modules_completion:timemodified'] = 'The time when the Typeform activity was completed';
$string['privacy:metadata:typeform'] = 'In order to integrate with Typeform, user data needs to be exchanged with that service.';
$string['privacy:metadata:typeform:userid'] = 'The user ID is sent from Moodle to allow you to access your data on Typeform';

// Capabilities
$string['typeform:view'] = 'View Typeform';
$string['typeform:addinstance'] = 'Add a new Typeform activity';

// Test connection
$string['configuration'] = 'Configuration';
$string['testresults'] = 'Test Results';
$string['testingconnection'] = 'Testing API connection';
$string['testingforms'] = 'Testing forms retrieval';
$string['testingworkspaces'] = 'Testing workspaces';
$string['connectionsuccessful'] = 'Connection successful!';
$string['connectionfailed'] = 'Connection failed. Please check your API token.';
$string['formsfound'] = 'Found {$a} form(s)';
$string['noformsfound'] = 'No forms found.';
$string['andmoreforms'] = '... and {$a} more form(s)';
$string['allworkspacesvalid'] = 'All {$a} workspace(s) are valid';
$string['someworkspacesinvalid'] = 'Some workspaces are invalid: {$a}';
$string['alltestspassed'] = 'All {$a} test(s) passed successfully!';
$string['sometestsfailed'] = 'Some tests failed: {$a->passed} passed, {$a->failed} failed';
$string['allworkspaces'] = 'All workspaces (none configured)';
$string['summary'] = 'Summary';
$string['back'] = 'Back';
$string['completionsubmit'] = 'Make a submission';
$string['attemptfinished'] = 'Attempt finished';
$string['attemptstarted'] = 'Attempt started';
$string['eventnotexists'] = 'Event not exists';
$string['cmnotexists'] = 'cm not exists';
$string['alreadyexist'] = 'Already exist';
$string['privacy:metadata:typeform_submission'] = 'Information about user submissions in the Typeform activity.';
$string['privacy:metadata:typeform_submission:typeform'] = 'The ID of the Typeform activity instance the submission belongs to.';
$string['privacy:metadata:typeform_submission:userid'] = 'The ID of the user who made the submission.';
$string['privacy:metadata:typeform_submission:submitted'] = 'Whether the submission has been marked as submitted/completed.';
$string['privacy:metadata:typeform_submission:timecreated'] = 'The date/time when the submission record was created.';
$string['privacy:metadata:typeform_submission:timemodified'] = 'The date/time when the submission record was last modified.';
$string['privacy:metadata:typeform_submission:usermodified'] = 'The user who last modified the submission record (if applicable).';
$string['apiurl'] = 'Api url';
$string['apiurl_desc'] = 'Typeform api url ended by /';
$string['typeformjslink'] = 'JS link';
$string['typeformjslink_desc'] = 'Typefrom javascript link';
$string['typeformdomain'] = 'Domain';
$string['typeformdomain_desc'] = 'Typefrom domain';
$string['fworkspace'] = 'Select Typeform Workspace';
