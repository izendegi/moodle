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
 * Strings for component 'enrol_database', language 'en'.
 *
 * @package   enrol_database
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['autocreatecategory'] = 'Create course categories';
$string['autocreatecategory_desc'] = 'If courses are automatically created and they belong to a category that does not exist in Moodle yet, those categories can be created automatically';
$string['categoryseparator'] = 'Category separator';
$string['categoryseparator_desc'] = 'Leave this empty if you do not want to use subcategories in your external database. Otherwise, specify the character you are using as the category separator. You need to specify the \'path\' of the subcategory (in the \'New course category\' field) as the identifiers of the categories separated by the category separator. For example, if we use \'/\' as the separator, we should have something like category1/category2 (i.e., category2 is inside category category1, which is a top level category)';
$string['database:config'] = 'Configure database enrol instances';
$string['database:unenrol'] = 'Unenrol suspended users';
$string['dbencoding'] = 'Database encoding';
$string['dbhost'] = 'Database host';
$string['dbhost_desc'] = 'Type database server IP address or host name. Use a system DSN name if using ODBC. Use a PDO DSN if using PDO.';
$string['dbname'] = 'Database name';
$string['dbname_desc'] = 'Leave empty if using a DSN name in database host.';
$string['dbpass'] = 'Database password';
$string['dbsetupsql'] = 'Database setup command';
$string['dbsetupsql_desc'] = 'SQL command for special database setup, often used to setup communication encoding - example for MySQL and PostgreSQL: <em>SET NAMES \'utf8\'</em>';
$string['dbsybasequoting'] = 'Use sybase quotes';
$string['dbsybasequoting_desc'] = 'Sybase style single quote escaping - needed for Oracle, MS SQL and some other databases. Do not use for MySQL!';
$string['dbtype'] = 'Database driver';
$string['dbtype_desc'] = 'ADOdb database driver name, type of the external database engine.';
$string['dbuser'] = 'Database user';
$string['debugdb'] = 'Debug ADOdb';
$string['debugdb_desc'] = 'Debug ADOdb connection to external database - use when getting empty page during login. Not suitable for production sites!';
$string['defaultcategory'] = 'Default new course category';
$string['defaultcategory_desc'] = 'The default category for auto-created courses. Used when no new course category field is specified or the category is not found (and \'Create course categories\' is not enabled.)';
$string['defaultrole'] = 'Default role';
$string['defaultrole_desc'] = 'The role that will be assigned by default if no other role is specified in external table.';
$string['ignorehiddencourses'] = 'Ignore hidden courses';
$string['ignorehiddencourses_desc'] = 'If enabled users will not be enrolled on courses that are set to be unavailable to students.';
$string['localcategoryfield'] = 'Local category field';
$string['localcoursefield'] = 'Local course field';
$string['localrolefield'] = 'Local role field';
$string['localtemplatefield'] = 'Local template field';
$string['localuserfield'] = 'Local user field';
$string['newcoursetable'] = 'Remote new courses table';
$string['newcoursetable_desc'] = 'Specify the name of the table that contains list of courses that should be created automatically. Empty means no courses are created.';
$string['newcoursecategory'] = 'New course category field';
$string['newcoursecategory_desc'] = 'This field is not being used by the template-based matriculation patch.';
$string['newcoursecategorypath'] = 'New course category path field';
$string['newcoursecategorypath_desc'] = 'The path of the category, which will be used to check if the category exists, taking into account the subcategories, if category separator given.';
$string['newcoursefullname'] = 'New course full name field';
$string['newcourseidnumber'] = 'New course ID number field';
$string['newcoursetemplate'] = 'New course template field';
$string['newcoursetemplate_desc'] = 'Auto-created courses can copy their settings from a template course. Specify the name of the field where the identifier of the template course (as specified in \'Local course field\' setting) is stored';
$string['newcourseshortname'] = 'New course short name field';
$string['newcoursesummary'] = 'New course summary field';
$string['pluginname'] = 'External database';
$string['pluginname_desc'] = 'You can use an external database (of nearly any kind) to control your enrolments. It is assumed your external database contains at least a field containing a course ID, and a field containing a user ID. These are compared against fields that you choose in the local course and user tables.';
$string['remotecoursefield'] = 'Remote course field';
$string['remotecoursefield_desc'] = 'The name of the field in the remote table that we are using to match entries in the course table.';
$string['remoteenroltable'] = 'Remote user enrolment table';
$string['remoteenroltable_desc'] = 'Specify the name of the table that contains list of user enrolments. Empty means no user enrolment sync.';
$string['remoteotheruserfield'] = 'Remote Other User field';
$string['remoteotheruserfield_desc'] = 'The name of the field in the remote table that we are using to flag "Other User" role assignments.';
$string['remoterolefield'] = 'Remote role field';
$string['remoterolefield_desc'] = 'The name of the field in the remote table that we are using to match entries in the roles table.';
$string['remoteuserfield'] = 'Remote user field';
$string['settingsheaderdb'] = 'External database connection';
$string['settingsheaderlocal'] = 'Local field mapping';
$string['settingsheaderremote'] = 'Remote enrolment sync';
$string['settingsheadernewcourses'] = 'Creation of new courses';
$string['syncenrolmentstask'] = 'Synchronise external database enrolments task';
$string['remoteuserfield_desc'] = 'The name of the field in the remote table that we are using to match entries in the user table.';
$string['templatecourse'] = 'New course template';
$string['templatecourse_desc'] = 'Optional: auto-created courses can copy their settings from a template course. Type here the shortname of the template course. Used when no new course template field is specified or the template course is not found';
$string['settingsheadernewgroups'] = 'Creation of new groups';
$string['newgrouptable'] = 'Remote new groups table';
$string['newgrouptable_desc'] = 'Specify the name of the table that contains list of groups that should be created automatically. Empty means no groups are created.';
$string['newgroupname'] = 'New group name field';
$string['newgroupidnumber'] = 'New group id number field';
$string['newgroupdesc'] = 'New group description field';
$string['newgroupgroupings'] = 'New group grouping field';
$string['newgroupgroupings_desc'] = 'The name of the field in the remote table that we are using to match entries in the groupings table.';
$string['groupingcreation'] = 'Enable new groupings creation';
$string['groupingcreation_desc'] = 'If enabled, the groupings specified in the external database that do not exist in the Moodle database will be created automatically.';
$string['newgroupcourse'] = 'New group course field';
$string['newgroupcourse_desc'] = 'The short name of the course to which the new group belongs.';
$string['groupmessaging'] = 'Enable group messaging';
$string['groupmessaging_desc'] = 'If enabled, group members can send messages to the others in their group via de messaging drawer.';
$string['settingsheadergroupenrol'] = 'Group enrolments sync';
$string['groupenroltable'] = 'Remote group enrolments table';
$string['groupenroltable_desc'] = 'Specify the name of the table that contains list of users that need to be added to a group. Empty means no group enrolments sync.';
$string['userfield'] = 'User name field';
$string['userfield_desc'] = 'The name of the field in the remote table that we are using to match entries in the user table.';
$string['groupfield'] = 'Group idnumber field';
$string['groupfield_desc'] = 'The name of the field in the remote table that we are using to match entries in the groups table.';
$string['privacy:metadata'] = 'The External database enrolment plugin does not store any personal data.';
