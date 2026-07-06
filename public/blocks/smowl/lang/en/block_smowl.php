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
 * Lang File.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'SMOWL';

// P&P manage.

$string['typeconfigheading'] = 'Type of configuration';
$string['typeconfigheadingdesc'] = 'Select the type of configuration <br> · Automatic <br> · Manual <br> · Configure later';
$string['typeconfigheadingdesc2'] = 'Next, if you wish, you can change your configuration type';

$string['typeconfig'] = 'Configuration type';
$string['typeconfigdesc'] = 'Select the action you want to perform next';
$string['noconfigmessage'] = 'You have selected to configure SMOWL later, you will be able to do it from the campus administration.';

$string['changetypeconfigauto'] = 'Switch to automatic settings.';
$string['changetypeconfigrestartauto'] = 'Restart automatic settings.';
$string['changetypeconfigmanual'] = 'Switch to manual settings.';
$string['changetypeconfigcancel'] = 'Cancel configuration change.';
$string['changetypeconfigafter'] = 'Configure later.';

$string['typeask'] = 'Select one option';
$string['typenoconfig'] = 'Configure later';
$string['typeautoconfig'] = 'Automatic settings';
$string['typemanualconfig'] = 'Manual settings';

$string['dataautoconfigheading'] = 'Auto configuration data';
$string['dataautoconfigheadingdesc'] = 'You will then be able to validate or insert the autoconfig data.';

$string['autoconfigmessagejsondataclient'] = 'Client information comes preconfigured in this plugin.';
$string['autoconfigmessageconfigdone'] = 'SMOWL plugin has been configured automatically';

$string['autoconfigmessageclientvoid'] = 'Client information must be configured correctly';
$string['autoconfigmessagecontenterror'] = 'Autoconfig error: Returned with invalid elements';
$string['autoconfigmessageunauthorized'] = 'Autoconfig error: Authentication problems';
$string['autoconfigmessagebadrequest'] = 'Autoconfig error: Bad Request';
$string['autoconfigmessageentityerror'] = 'Autoconfig error: Could not get entity';
$string['autoconfigmessageconflict'] = 'Autoconfig error: Entity data internal problems';

$string['clientid'] = 'Client identifier';
$string['clientiddesc'] = 'Client identifier to activate campus';

$string['clientkey'] = 'Activation key';
$string['clientkeydesc'] = 'Activation key to activate campus';

$string['nocapabilityconfigmessage'] = 'You do not have permissions to perform this action';

// Connection smowl settings.
$string['connectionsconfig'] = 'Connection settings';
$string['connectionsconfigdesc'] = 'Below you will find your platform\'s connection values with Smowltech.';
$string['connectionsconfigcontact'] = 'For more information about your current license, access your personal client area at <a href="https://my.smowltech.net" target="_blank">https://my.smowltech.net</a>.';

$string['entity'] = 'Platform';
$string['entitydesc'] = 'Unique identifier for this platform, provided by <a href="https://smowl.net/en/contact/" target="_blank">Smowltech</a>.';
$string['noticeemptyentitysettings'] = 'Platform is empty, contact Smowltech for value to enter.';

$string['password'] = 'License key';
$string['passworddesc'] = 'License key, provided by Smowltech.';
$string['noticeemptypasswordsettings'] = 'The license key is empty, contact Smowltech to know the value to enter.';

$string['apikey'] = 'API key';
$string['apikeydesc'] = 'API key, provided by Smowltech.';
$string['noticeemptyapikeysettings'] = 'API key is empty, contact Smowltech to know the value to enter.';

// JWT Secret
$string['smowljwtsecret'] = 'JWT Secret';
$string['smowljwtsecretdesc'] = 'JWT Secret, provided by Smowltech.';
$string['noticeemptysmowljwtsecret'] = 'JWT Secret is empty, contact Smowltech to know the value to enter.';

// Instructors smowl settings.
$string['instructorsconfig'] = 'Instructors settings';
$string['instructorsconfigdesc'] = 'The following options customize the use of SMOWL for instructors in courses where the block is enabled.';

$string['continuousassessment'] = 'Continuous assessment';
$string['onlyexams'] = 'Exams\' monitoring';

$string['tracking'] = 'Monitoring type';
$string['trackingdesc'] = 'If the checked type is “Exam Monitoring” SMOWL can only be activated on quizzes.<br>If the checked type is “Continuous Assessment” SMOWL will be available for all activities provided in MOODLE.';

$string['attempttracking'] = 'Distinction of exam attempts';
$string['attempttrackingdesc'] = 'Select if you want to differentiate between different attempts of the same exam for a student.';

// Settings advancer instructors.
$string['viewsettingsadvancedinstructorstit'] = 'Advanced settings for instructors';
$string['viewsettingsadvancedinstructors'] = 'View advanced settings for instructors';
$string['viewsettingsadvancedinstructorsdesc'] = 'Activating this option will display the advanced options for instructors.';

$string['accesscontrol'] = 'Access control';
$string['accesscontroldesc'] = 'Only allow users to access the exam if the SMOWL tools are active.';

// Settings advancer users.
$string['usersconfig'] = 'Users settings';
$string['usersconfigdesc'] = 'The following options customize the use of SMOWL for users in those activities and courses where the monitoring system is activated.';

$string['floatblock'] = 'Enable floating proctoring block';
$string['floatblockdesc'] = 'Select to view the floating block';
$string['activeinpopup'] = 'Supervision is available in the floating block';

$string['blockheight'] = 'Display size';
$string['blockheightdesc'] = 'Height of the iframe where the webcam will be displayed during monitoring.';
$string['noticeblockheight'] = 'The Height of the iframe can not be less than 280px.';

// Settings advancer users.
$string['viewsettingsadvanceduserstit'] = 'Advanced settings for users';
$string['viewsettingsadvancedusers'] = 'View advanced settings for users';
$string['viewsettingsadvancedusersdesc'] = 'Activating this option will display the advanced options for users.';

// Capabilities.
$string['smowl:addinstance'] = 'Add SMOWL block';
$string['smowl:manageactivities'] = 'Manage SMOWL activities';
$string['smowl:managegroups'] = 'Manage SMOWL groups';
$string['smowl:viewstudentcontent'] = 'View studentlinks';
$string['smowl:enrolment'] = 'Access to SMOWL enrolment';

// Notices.
$string['notinstancedblockincourse'] = 'To perform this action you must create the SMOWL block in the course.';
$string['notmanagepermissions'] = 'You do not have permission to manage SMOWL activities.';
$string['notteachersmanagementpermissions'] = 'Teachers are not allowed to manage SMOWL activities.';
$string['notviewmanagepermissions'] = 'You do not have permission to view or manage SMOWL activities.';
$string['cannotcreatefile'] = 'The file could not be created';
$string['activitiesupdate'] = 'SMOWL activities updated';
$string['activityupdate'] = 'SMOWL activity updated';
$string['noticeemptysmowlconfig'] = 'The configuration of the block has not been completed. <br/>'.
    'Contact the platform administrator to solve this problem.';
$string['noticeemptyentitynavigation'] = 'The configuration of the SMOWL block has not been completed. <br/>'.
    'Contact the platform administrator to solve this problem.';

// Privacy.
$string['privacy:metadata'] = 'SMOWL system only displays data stored on the Smowltech\'s servers.';
$string['privacy:metadata:smowl:smowltech_net'] = 'SMOWL system only displays data stored and transmits user data from Moodle to the Smowltech\'s servers.';
$string['privacy:metadata:smowl:smowltech_net:user_id'] = 'Unique identifier of user';

// Events.
$string['eventinstancecreated'] = 'Event created block instance';
$string['createblockinstance'] = 'Created block instance: ';
$string['eventinstancedeleted'] = 'Event deleted block instance';
$string['deleteblockinstance'] = 'Deleted block instance: ';
$string['eventinstanceupdated'] = 'Event updated block instance';
$string['updateblockinstance'] = 'Updated block instance ';
$string['fromoldblockname'] = ' from old block ';
$string['apicalled'] = 'SMOWL API called';
$string['apicalleddesc'] = 'SMOWL API settings called.';

// Internal URL SMOWL Params.
$string['internalconfig'] = 'Internal SMOWL settings';
$string['internalconfigdesc'] = 'The following options may affect the operation of the plugin,'.
    ' some of them must be modified only at the express request of SMOWL';
$string['onlysmowlexpressrequest'] = 'This option should be modified only at the express request of SMOWL.';
$string['viewsettingsinternal'] = 'View Internal options';
$string['viewsettingsinternaldesc'] = 'Active this check, to see the Internal SMOWL options.';

$string['internalconfigurls'] = 'Internal SMOWL URL settings';
$string['urlstudentview'] = 'Student view link';
$string['urlstudentviewdesc'] = 'Link to student view.';

// API URLs.
$string['internalconfigapiurls'] = 'SMOWL API URL settings';
$string['urlsmowlapi'] = 'SMOWL API link';
$string['urlsmowlapidesc'] = 'Link to access to the SMOWL API.';

$string['apilmssettings'] = 'Update LMS settings';
$string['apilmssettingsdesc'] = 'URL to update LMS settings.';

$string['apilmssettingscustomer'] = 'Update LMS settings automatic';
$string['apilmssettingscustomerdesc'] = 'URL to update LMS settings automatic.';

$string['apiconfigclient'] = 'Integration activation';
$string['apiconfigclientdesc'] = 'URL to obtains integration activation data.';

$string['apiaddactivity'] = 'Add activity';
$string['apiaddactivitydesc'] = 'URL to add activity proctored';

$string['apimodifyactivity'] = 'Modify activity';
$string['apimodifyactivitydesc'] = 'URL to modify activity proctored';

// Accessrule smowlcheckcam Settings.
$string['accesrulesmowlcheckcamconfig'] = 'Configuration of SMOWL access rules';
$string['accesrulesmowlcheckcamconfigdesc'] = 'The following options affect the access rules to display in SMOWL quizzes.';
$string['accesrulesmowlcheckcam'] = 'Enable camera validation for student';
$string['accesrulesmowlcheckcamdesc'] = 'If camera validation is activated, the student will be forced to validate that his camera is working correctly before accessing the quizzes.';

// View smowl settings.
$string['viewconfig'] = 'View settings';
$string['viewconfigdesc'] = 'The following options affect the display of the block ';
$string['floatsnap'] = 'Enable floating block in the Snap theme';
$string['floatsnapdesc'] = 'Select to view the floating block, it only works for SNAP theme';

// Manage SMOWL Groups.
$string['managegroups'] = 'Groups\' management';
$string['groupsaccessrestrictions'] = 'Access restrictions';

$string['managegroupsformintro'] = 'In the following form, you must select the user groups or groupings to which the SMOWL block will be displayed.';
$string['managegroupsupdate'] = 'Groups with SMOWL active correctly updated.';

$string['availabilityconditionsjsonform'] = 'Access restrictions';
$string['availabilityconditionsjsonform_help'] = 'From this menu you can add the necessary access restrictions.';

// Setting Manage Groups.
$string['notmanagegroupspermissions'] = 'You do not have permissions to manage groups.';
$string['managegroupsnotconfigured'] = 'The manage groups is not configured.';

// Bulk actions.

$string['bulkactions'] = 'Bulk Actions';
$string['bulkactionsdesc'] = 'If activated, you can manage the search and activation of SMOWL in activities, from the front page of the campus.';
$string['noticeactivebulkactions'] = 'To activate this functionality, you must go to "Internal options" of SMOWL';
$string['bulkactive'] = 'Activities activation';
$string['bulkgroups'] = 'Groups activation';
$string['coursecategory'] = 'Course category';
$string['coursecategory_help'] = 'Course category to active SMOWL.';
$string['activitytype'] = 'Activity type';
$string['activitytype_help'] = 'Acivity type to active SMOWL.';
$string['activityname'] = 'Activity name';
$string['activityname_help'] = 'Acivity name to active SMOWL.';
$string['searchactivities'] = 'Search activities';
$string['allcategories'] = 'All categories';
$string['searchresults'] = 'Search results';
$string['notfound'] = 'Results not found';
$string['savechanges'] = 'Save Changes';
$string['bulkactiveupdate'] = 'SMOWL bulk activities\' settings correctly updated';
$string['searchgroups'] = 'Search groups';
$string['groupname'] = 'Group name';
$string['groupname_help'] = 'Group name to active SMOWL.';
$string['managebulkgroupsformintro'] = 'In the following form you can see all the groups resulting from the search.';
$string['managebulkgroupsforminfo'] = 'Users belonging to any of the selected groups will be monitored by SMOWL.';
$string['managebulkgroupsformmoreinfo'] = 'IMPORTANT:<br/>The group assignment from this form will overwrite the group assignment applied in each of the courses involved.';
$string['notviewmanagebuklpermissions'] = 'You do not have permission to view or manage bulk SMOWL activities.';

// Access Control Status.
$string['acwaiting'] = 'Checking SMOWL,  please wait';
$string['acaccess'] = 'SMOWL is engaged, you can access your activity';
$string['acnotaccess'] = 'SMOWL is not correctly engaged, refresh your browser';

// LTI Integration.
$string['internalconfigltitool'] = 'SMOWL LTI URL settings';
$string['ltitoolname'] = 'SMOWL panel';
$string['urlsmowlltitool'] = 'LTI URL Base';
$string['urlsmowlltitooldesc'] = 'SMOWL LTI tool URL base';
$string['ltitoolinit'] = 'URL inicial';
$string['ltitoolinitdesc'] = 'SMOWL LTI tool URL inicial';
$string['ltitoolversion'] = 'LTI Version';
$string['ltitoolversiondesc'] = 'SMOWL LTI tool version';
$string['ltitoolpublickeyset'] = 'Public keyset';
$string['ltitoolpublickeysetdesc'] = 'SMOWL LTI tool public keyset URL';
$string['ltitoolinitiatelogin'] = 'Initiate login URL';
$string['ltitoolinitiatelogindesc'] = 'SMOWL LTI tool initiate login URL';
$string['ltitoolredirection'] = 'Redirection URI';
$string['ltitoolredirectiondesc'] = 'SMOWL LTI tool redirection URI';
$string['ltitoolconfigusage'] = 'Configuration usage';
$string['ltitoolconfigusagedesc'] = 'SMOWL LTI tool configuration usage as "Show as preconfigured tool when adding an external tool"';
$string['ltitoollaunch'] = 'Default launch container';
$string['ltitoollaunchdesc'] = 'SMOWL LTI tool default launch container as "Embed, without blocks"';
$string['ltitoolconfigmemberships'] = 'Default memberships';
$string['ltitoolconfigmembershipsdesc'] = 'SMOWL LTI tool default launch container as "Embed, without blocks"';

$string['urlsmowlltiapi'] = 'LTI API URL Base';
$string['urlsmowlltiapidesc'] = 'SMOWL LTI API URL base';
$string['ltiapiapplications'] = 'LTI Applications';
$string['ltiapiapplicationsdesc'] = 'SMOWL LTI API applications';
$string['ltiapideployments'] = 'LTI Deployments';
$string['ltiapideploymentsdesc'] = 'SMOWL LTI API deployments';

// LTI problems.
$string['lticreatetoolsuccess'] = 'Success creating LTI SMOWL tool';
$string['ltiupdatetoolsuccess'] = 'Success updating LTI SMOWL tool';
$string['lticreatetoolerror'] = 'There are problems creating LTI SMOWL tool';
$string['ltisendtoolerror'] = 'There are problems sending LTI config to SMOWL';
$string['ltisendtoolneedactivation'] = 'Attention! It seems that your entity has not yet been validated in the mySmowltech app, which is preventing the completion of the plugin configuration. <a href="https://my.smowltech.net/" target="_blank">Click here</a> and log in with your access credentials to mySmowltech to validate your entity. This validation is necessary to complete the integration correctly.';
$string['lticreatewserror'] = 'There are problems creating SMOWL WS';
$string['ltiactivewserror'] = 'There are problems activating SMOWL WS';
$string['lticreateusererror'] = 'There are problems creating the user "Smowl Webservices User". An administrator action is required to activate it.';
$string['noticeltinotvisible'] = 'LTI tools are locked in campus administration (Plugins / Manage activities).';
$string['ltisendwserror'] = 'There are problems sending WS information';
$string['ltiltiactivityerror'] = 'There are problems creating SMOWL LTI Activity';
$string['notlticourse'] = 'Do not have LTI in course.';

// LTI internal config.
$string['internalconfiglticonfig'] = 'SMOWL LTI internal settings';
$string['ltientity'] = 'LTI Entity';
$string['ltitypeid'] = 'LTI type ID';
$string['ltideploymentid'] = 'LTI deployment';
$string['ltiappid'] = 'LTI app';
$string['ltirestid'] = 'LTI REST';

$string['ltiactivityname'] = 'SMOWL panel';

// Block links teacher.
$string['ltimanagesmowl'] = 'Monitoring dashboard';
$string['teachercontent'] = 'Set up monitoring and review results';
$string['teacherbutton'] = 'Access SMOWL';

// Block links Student.
$string['ltistudentsmowl'] = 'SMOWL panel';
$string['studentcontent'] = 'Access the SMOWL registration and download panel';
$string['studentbutton'] = 'Access SMOWL';

$string['notstudentaccesspermissions'] = 'Only students can access this section';

// Corner
$string['drag_me'] = 'Drag me';