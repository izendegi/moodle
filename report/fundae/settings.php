<?php

use core\notification;

defined('MOODLE_INTERNAL') || die;
global $ADMIN;
if ($hassiteconfig) {
    $settingspage = new admin_settingpage('report_fundae', new lang_string('pluginname', 'report_fundae'));

    $settingspage->add(new admin_setting_heading('report_fundae/general',
        new lang_string('general', 'report_fundae'), ''));

    $name = 'report_fundae/teachersseereports';
    $label = new lang_string('enableteachersseereports', 'report_fundae');
    $description = new lang_string('enableteachersseereports_desc', 'report_fundae');
    $default = 1;
    $setting = new admin_setting_configcheckbox($name, $label, $description, $default);
    $settingspage->add($setting);

    $name = 'report_fundae/studentsseereports';
    $label = new lang_string('enablestudentsseereports', 'report_fundae');
    $description = new lang_string('enablestudentsseereports_desc', 'report_fundae');
    $default = 1;
    $setting = new admin_setting_configcheckbox($name, $label, $description, $default);
    $settingspage->add($setting);

    $name = 'report_fundae/bbbreports';
    $label = new lang_string('enablebbbstatistics', 'report_fundae');
    $description = new lang_string('enablebbbstatistics_desc', 'report_fundae');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $label, $description, $default);
    $settingspage->add($setting);


    $settingspage->add(new admin_setting_heading('report_fundae/sessiontimehdr',
        new lang_string('timetracking', 'report_fundae'), ''));

    // Session threshold.
    $name = 'report_fundae/sessionthreshold';
    $label = new lang_string('sessionthreshold', 'report_fundae');
    $description = new lang_string('sessionthreshold_desc', 'report_fundae');
    $default = 20;
    $type = PARAM_INT;
    $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
    $settingspage->add($setting);

    $settingspage->add(new admin_setting_heading('report_fundae/reportgenerationhdr',
        new lang_string('reportgeneration', 'report_fundae'), ''));

    // Enable or disable periodic report generation.
    $name = 'report_fundae/enablescheduledreportgeneration';
    $label = new lang_string('enablescheduledreportgeneration', 'report_fundae');
    $description = new lang_string('enablescheduledreportgeneration_desc', 'report_fundae');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $label, $description, $default);
    $settingspage->add($setting);

    // Scheduled bulk report generation mode.
    $name = 'report_fundae/bulkscheduledreportgenerationmode';
    $label = new lang_string('bulkscheduledreportgenerationmode', 'report_fundae');
    $description = new lang_string('bulkscheduledreportgenerationmode_desc', 'report_fundae');
    $default = 0;
    $options = [
        0 => get_string('disabled', 'report_fundae'),
        1 => get_string('allcourses', 'report_fundae'),
        2 => get_string('allcoursesandactivities', 'report_fundae'),
    ];
    $setting = new admin_setting_configselect($name, $label, $description, $default, $options);
    $settingspage->add($setting);
    $settingspage->hide_if('report_fundae/bulkscheduledreportgenerationmode', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

    // Scheduled report mailing.
    $name = 'report_fundae/scheduledreportmailing';
    $label = new lang_string('scheduledreportmailing', 'report_fundae');
    $description = new lang_string('scheduledreportmailing_desc', 'report_fundae');
    $default = '';
    $type = PARAM_TEXT;
    $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
    $settingspage->add($setting);
    $settingspage->hide_if('report_fundae/scheduledreportmailing', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

    // Pick periodic report format among dataformats.
    $name = 'report_fundae/scheduledreportgenerationformat';
    $label = new lang_string('scheduledreportgenerationformat', 'report_fundae');
    $description = new lang_string('scheduledreportgenerationformat_desc', 'report_fundae');
    $default = 'csv';
    $options = [
        'csv' => 'csv',
        'xlsx' => 'xlsx',
        'ods' => 'ods',
    ];
    $setting = new admin_setting_configselect($name, $label, $description, $default, $options);
    $settingspage->add($setting);
    $settingspage->hide_if('report_fundae/scheduledreportgenerationformat', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

    // Reports upload
    $ftpextension = extension_loaded ('ftp');
    $sftpextension = extension_loaded ('ssh2');
    $page_config = optional_param('section', '', PARAM_TEXT);
    if ($ftpextension !== true && $page_config === 'report_fundae' ) {
        notification::warning(get_string('ftpnoextension', 'report_fundae'));
    }
    if ($sftpextension !== true && $page_config === 'report_fundae') {
        notification::warning(get_string('sftpnoextension', 'report_fundae'));
    }
    if ($ftpextension || $sftpextension) {
        $settingspage->add(new admin_setting_heading('report_fundae/ftpconfighdr',
                new lang_string('ftpconfig', 'report_fundae'),
                get_string('ftpinformation', 'report_fundae'))
        );
        $settingspage->hide_if('report_fundae/ftpconfighdr', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);
        // Type Host.
        $name = 'report_fundae/typehost';
        $label = new lang_string('typehost', 'report_fundae');
        $description = new lang_string('typehost_help', 'report_fundae');
        $default = 0;
        $options = [
            0 => 'SFTP',
            1 => 'FTP',
            2 => 'Google Drive',
        ];
        $setting = new admin_setting_configselect($name, $label, $description, $default, $options);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/typehost', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

        // Host.
        $name = 'report_fundae/ftphost';
        $label = new lang_string('ftphost', 'report_fundae');
        $description = new lang_string('ftphost_help', 'report_fundae');
        $default = '';
        $type = PARAM_HOST;
        $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/ftphost', 'report_fundae/typehost', 'eq', 2);
        $settingspage->hide_if('report_fundae/ftphost', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

        // Port.
        $name = 'report_fundae/ftpport';
        $label = new lang_string('ftpport', 'report_fundae');
        $description = new lang_string('ftpport_help', 'report_fundae');
        $default = 21;
        $type = PARAM_INT;
        $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/ftpport', 'report_fundae/typehost', 'eq', 2);
        $settingspage->hide_if('report_fundae/ftpport', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

        // Username.
        $name = 'report_fundae/ftpuser';
        $label = new lang_string('ftpuser', 'report_fundae');
        $description = new lang_string('ftpuser_help', 'report_fundae');
        $default = '';
        $type = PARAM_RAW;
        $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/ftpuser', 'report_fundae/typehost', 'eq', 2);
        $settingspage->hide_if('report_fundae/ftpuser', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

        // Password.
        $name = 'report_fundae/ftppassword';
        $label = new lang_string('ftppassword', 'report_fundae');
        $description = new lang_string('ftppassword_help', 'report_fundae');
        $default = '';
        $setting = new admin_setting_configpasswordunmask($name, $label, $description, $default);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/ftppassword', 'report_fundae/typehost', 'eq', 2);
        $settingspage->hide_if('report_fundae/ftppassword', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

        // Remote FIle.
        $name = 'report_fundae/ftpremotepath';
        $label = new lang_string('ftpremotepath', 'report_fundae');
        $description = new lang_string('ftpremotepath_help', 'report_fundae');
        $default = '';
        $type = PARAM_RAW;
        $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/ftpremotepath', 'report_fundae/typehost', 'eq', 2);
        $settingspage->hide_if('report_fundae/ftpremotepath', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

        // Credentials Json
        // JSON
        $name = 'report_fundae/credentialsjson';
        $label = new lang_string('credentialsjson', 'report_fundae');
        $description = new lang_string('credentialsjson_help', 'report_fundae');
        $default = '';
        $type = PARAM_RAW;
        $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/credentialsjson', 'report_fundae/typehost', 'eq', 0);
        $settingspage->hide_if('report_fundae/credentialsjson', 'report_fundae/typehost', 'eq', 1);
        $settingspage->hide_if('report_fundae/credentialsjson', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

        // Client ID
        $name = 'report_fundae/clientid';
        $label = new lang_string('clientid', 'report_fundae');
        $description = new lang_string('clientid_help', 'report_fundae');
        $default = '';
        $type = PARAM_RAW;
        $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/clientid', 'report_fundae/typehost', 'eq', 0);
        $settingspage->hide_if('report_fundae/clientid', 'report_fundae/typehost', 'eq', 1);
        $settingspage->hide_if('report_fundae/clientid', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

        // Client ID
        $name = 'report_fundae/clientsecret';
        $label = new lang_string('clientsecret', 'report_fundae');
        $description = new lang_string('clientsecret_help', 'report_fundae');
        $default = '';
        $type = PARAM_RAW;
        $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/clientsecret', 'report_fundae/typehost', 'eq', 0);
        $settingspage->hide_if('report_fundae/clientsecret', 'report_fundae/typehost', 'eq', 1);
        $settingspage->hide_if('report_fundae/clientsecret', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

        // Folder ID
        $name = 'report_fundae/folderid';
        $label = new lang_string('folderid', 'report_fundae');
        $description = new lang_string('folderid_help', 'report_fundae');
        $default = '';
        $type = PARAM_RAW;
        $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/folderid', 'report_fundae/typehost', 'eq', 0);
        $settingspage->hide_if('report_fundae/folderid', 'report_fundae/typehost', 'eq', 1);
        $settingspage->hide_if('report_fundae/folderid', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

        // Historic Folder ID
        $name = 'report_fundae/historicfolderid';
        $label = new lang_string('historicfolderid', 'report_fundae');
        $description = new lang_string('historicfolderid_help', 'report_fundae');
        $default = '';
        $type = PARAM_RAW;
        $setting = new admin_setting_configtext($name, $label, $description, $default, $type);
        $settingspage->add($setting);
        $settingspage->hide_if('report_fundae/historicfolderid', 'report_fundae/typehost', 'eq', 0);
        $settingspage->hide_if('report_fundae/historicfolderid', 'report_fundae/typehost', 'eq', 1);
        $settingspage->hide_if('report_fundae/historicfolderid', 'report_fundae/enablescheduledreportgeneration', 'eq', 0);

    } else {
        notification::error(get_string('enableextensions', 'report_fundae'));
    }

    $ADMIN->add('reports', $settingspage);
}
