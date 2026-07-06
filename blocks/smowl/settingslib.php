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
 * Settings File.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/adminlib.php');

/**
 * Add SMOWL Header to setting.
 * @param object $settings
 * @param string $name option string code
 * @param string $code optional string code
 * @param string $desc optional description string
 */
function block_smowl_settings_add_header($settings, $name, $code = '', $desc = '') {
    if (!$code) {
        $code = 'smowl_' . $name . '_heading';
    }
    switch ($desc) {
        case 'VOID':
            $desc = '';
            break;
        case 'internalconfigdesc':
            global $OUTPUT;
            $desc = $OUTPUT->notification(get_string('internalconfigdesc', 'block_smowl'), 'danger');
            break;
        case '':
            $desc = get_string($name . 'desc', 'block_smowl');
            break;
    }
    // For alerts without title $name = ''.
    $strname = $name;
    if ($name) {
        $strname = get_string($name, 'block_smowl');
    }

    $header = new admin_setting_heading($code, $strname, $desc);
    $settings->add($header);
}

/**
 * Show advanced settings section.
 * @param object $settings
 * @param string $name option string code
 */
function block_smowl_settings_advanced_section($settings, $name) {
    $default = 0;
    $option = 'block_smowl/' . $name;
    $strname = get_string($name, 'block_smowl');
    $desc = get_string($name . 'desc', 'block_smowl');

    $advanced = new admin_setting_configcheckbox($option, $strname, $desc, $default);
    $advanced->set_updatedcallback('block_smowl_settings_check_changes');
    $settings->add($advanced);
}

/**
 * Add SMOWL text setting.
 * @param object $settings
 * @param string $name option string code
 * @param string $default default value
 * @param string $type of value
 * @param string $updatecallback function callback
 * @param string $description optional description string
 */
function block_smowl_settings_add_text($settings, $name, $default, $type, $updatecallback, $description = '') {
    $option = 'block_smowl/' . $name;
    $strname = get_string($name, 'block_smowl');

    if ($description == 'VOID') {
        $description = '';
    } else {
        $description = get_string($name . 'desc', 'block_smowl');
    }

    if ($name == 'clientkey') {
        $text = new block_smowl_admin_setting_clientkey('block_smowl/clientkey', $name, $description, $default, $type);
    } else {
        $text = new admin_setting_configtext($option, $strname, $description, $default, $type);
    }

    if ($updatecallback) {
        $text->set_updatedcallback('block_smowl_settings_check_changes');
    }
    $settings->add($text);
}

/**
 * Add SMOWL select setting.
 * @param object $settings
 * @param string $name option string code
 * @param string $default default value
 * @param array $arrayselect values of select
 * @param bool $updatecallback add to function callback
 * @param string $callback function callback name
 * @param bool $alertdesc show alert in description
 */
function block_smowl_settings_add_select($settings, $name, $default, $arrayselect, $updatecallback, $callback = '', $alertdesc = false) {
    global $OUTPUT;
    $option = 'block_smowl/' . $name;
    $strname = get_string($name, 'block_smowl');
    $description = "";
    if ($alertdesc) {
        $description = $OUTPUT->notification(get_string('onlysmowlexpressrequest', 'block_smowl'), 'alert alert-info');
    }
    $description .= get_string($name . 'desc', 'block_smowl');
    $select = new admin_setting_configselect($option, $strname, $description, $default, $arrayselect);
    if ($updatecallback) {
        if ($callback == '') {
            $select->set_updatedcallback('block_smowl_settings_check_changes');
        }else {
            $select->set_updatedcallback($callback);
        }
    }
    $settings->add($select);

}

/**
 * Add type instalation settings.
 * @param object $settings
 */
function block_smowl_settings_installation_type($settings) {
    $typeconfig = get_config('block_smowl', 'typeconfig');
    $typeconfigprev = get_config('block_smowl', 'typeconfigprev');

    $header = block_smowl_settings_add_header($settings, 'typeconfigheading');

    $selecttypeconfig = [];
    $selecttypeconfig[1] = get_string('typeautoconfig', 'block_smowl');
    $selecttypeconfig[2] = get_string('typemanualconfig', 'block_smowl');
    $selecttypeconfig[3] = get_string('typenoconfig', 'block_smowl');

    $default = 1;
    block_smowl_settings_add_select($settings, 'typeconfig', $default, $selecttypeconfig, false);
}

/**
 * Set change installation type.
 * @param object $settings
 * @param int $change
 */
function block_smowl_settings_installation_type_redirect($settings, $change = 0) {
    $typeconfig = get_config('block_smowl', 'typeconfig');
    $typeconfigprev = get_config('block_smowl', 'typeconfigprev');

    $titleconfigsettings = '';
    $texttype = '';
    $params = [];
    if ($change) {
        if ($typeconfigprev) {
            return;
        }
        if ($typeconfig == 1) {
            if (block_smowl_check_entity()) {
                $texttype = 'changetypeconfigrestartauto';
                $params['action'] = 'auto';
            } else {
                $texttype = 'changetypeconfigmanual';
                $params['action'] = 'manual';
            }
        } else {
            $texttype = 'changetypeconfigauto';
            $params['action'] = 'auto';
        }
        $text = get_string($texttype, 'block_smowl');
        $url = new moodle_url('/blocks/smowl/settingsshift.php', $params);
        $content = html_writer::link($url, $text);
        block_smowl_settings_add_header($settings, '', 'smowl_changetypeconfig_heading', $content);
        return;
    }

    if ($typeconfig && $typeconfigprev) {
        $texttype = 'changetypeconfigcancel';
        $params['action'] = 'cancel';
    } else {
        $texttype = 'changetypeconfigafter';
        $params['action'] = 'after';
    }

    $text = get_string($texttype, 'block_smowl');
    $url = new moodle_url('/blocks/smowl/settingsshift.php', $params);
    $content = html_writer::link($url, $text);
    block_smowl_settings_add_header($settings, '', 'smowl_typeconfig_heading', $content);
}

/**
 * Add type instalation config and redirect.
 */
function block_smowl_settings_change_configtype() {
    global $CFG;
    $typeconfig = get_config('block_smowl', 'typeconfig');
    if ($typeconfig == 3) {
        set_config('typeconfig', null, 'block_smowl');
        $message = get_string('noconfigmessage', 'block_smowl');
        redirect($CFG->wwwroot.'/?redirect=0', $message, null, \core\output\notification::NOTIFY_INFO);
    }
}

/**
 * Add client connection settings (automatic configuration).
 * @param object $settings
 * @param int $fromjson
 */
function block_smowl_settings_client_connection($settings, $fromjson = null) {

    global $CFG, $PAGE, $OUTPUT;
    require_once($CFG->dirroot. '/blocks/smowl/classes/setting_clientkey.php');

    block_smowl_settings_add_header($settings, 'dataautoconfigheading', 'smowl_dataautoconfig_heading');

    $autoconfigerror = get_config('block_smowl', 'autoconfigerror');

    if ($autoconfigerror) {
        $message = $autoconfigerror;
        if (strpos($autoconfigerror, ' ') === false) {
            $message = get_string('autoconfigmessage' . $autoconfigerror, 'block_smowl');
        }
        set_config('autoconfigerror', null, 'block_smowl');
        set_config('clientvalidated', null, 'block_smowl');

        $desc = $OUTPUT->notification($message, 'alert alert-warning');
        block_smowl_settings_add_header($settings, '', 'smowl_automaticconfigdone_heading', $desc);
    }
    block_smowl_settings_installation_type_redirect($settings, 1);
    $defaultclientid = '';
    $defaultclientkey = '';
    if ($fromjson && isset($fromjson->client_id) && isset($fromjson->client_key)) {
        $defaultclientid = $fromjson->client_id;
        $defaultclientkey = $fromjson->client_key;

        $clientid = get_config('block_smowl', 'clientid');
        $clientkey = get_config('block_smowl', 'clientkey');

        $desc = $OUTPUT->notification(get_string('autoconfigmessagejsondataclient', 'block_smowl'), 'alert alert-info');
        block_smowl_settings_add_header($settings, '', 'smowl_jsondata_heading', $desc);
    }

    block_smowl_settings_add_text($settings, 'clientid', $defaultclientid, PARAM_ALPHANUM, false);

    // SMOWL Password Setting.
    block_smowl_settings_add_text($settings, 'clientkey', $defaultclientkey, PARAM_RAW, false);

    block_smowl_settings_installation_type_redirect($settings);
}

/**
 * Show entity connection settings.
 * @param object $settings
 */
function block_smowl_settings_entity_connection($settings) {
    global $PAGE, $OUTPUT;
    $typeconfig = get_config('block_smowl', 'typeconfig');
    $lticonfig = block_smowl_lti_check();
    $lticonfigmessage = get_config('block_smowl', 'lticonfigmessage');

    if (!$lticonfig && $lticonfigmessage) {
        $message = $lticonfigmessage;
        if (strpos($lticonfigmessage, ' ') === false) {
            $message = get_string($lticonfigmessage, 'block_smowl');
        }
        $desc = $OUTPUT->notification($message, 'alert alert-info');
        block_smowl_settings_add_header($settings, '', 'smowl_automaticltifail_heading', $desc);
    }

    block_smowl_settings_installation_type_redirect($settings, 1);

    // Connection smowl settings.
    $connectionsconfigdesc = get_string('connectionsconfigdesc', 'block_smowl') .
    '<br>' . get_string('connectionsconfigcontact', 'block_smowl');

    block_smowl_settings_add_header($settings, 'connectionsconfig', '', $connectionsconfigdesc);

    if (block_smowl_check_entity() && $typeconfig == 2) {
        $desc = get_string('changetypeconfigauto', 'block_smowl');
        block_smowl_settings_add_header($settings, '', 'smowl_reconfig_heading', $desc);
    }

    $default = '';
    // SMOWL Entity Setting.
    block_smowl_settings_add_text($settings, 'entity', $default, PARAM_ALPHANUM, true);

    // SMOWL Password Setting.
    block_smowl_settings_add_text($settings, 'password', $default, PARAM_RAW, true);

    // APIKey Setting.
    block_smowl_settings_add_text($settings, 'apikey', $default, PARAM_RAW, true);

    // JWT Secret
    block_smowl_settings_add_text($settings, 'smowljwtsecret', $default, PARAM_RAW, true);

    block_smowl_settings_installation_type_redirect($settings);
}

/**
 * Show entity instructors settings.
 * @param object $settings
 */
function block_smowl_settings_instructors($settings) {
    global $CFG, $PAGE, $OUTPUT;
    // Instructors smowl settings.
    block_smowl_settings_add_header($settings, 'instructorsconfig');

    $selectyesno = [];
    $selectyesno[0] = get_string('no');
    $selectyesno[1] = get_string('yes');

    // Access control.
    $default = 1;
    block_smowl_settings_add_select($settings, 'accesscontrol', $default, $selectyesno, true);

    $selecttracking = [];
    $selecttracking[0] = get_string('onlyexams', 'block_smowl');
    $selecttracking[1] = get_string('continuousassessment', 'block_smowl');

    $default = 0;
    block_smowl_settings_add_select($settings, 'tracking', $default, $selecttracking, true, 'block_smowl_check_modules_track');

    if (!get_config('block_smowl', 'viewsettingsadvancedinstructors')) {
        block_smowl_settings_advanced_section($settings, 'viewsettingsadvancedinstructors');
    }
    if (get_config('block_smowl', 'viewsettingsadvancedinstructors')) {
        block_smowl_settings_add_header($settings, 'viewsettingsadvancedinstructorstit', 'smowl_advteach_heading', 'VOID');
        block_smowl_settings_advanced_section($settings, 'viewsettingsadvancedinstructors');

        // Bulk.
        block_smowl_settings_add_select($settings, 'bulkactions', $default, $selectyesno, true);

        // Attempt Tracking.
        block_smowl_settings_add_select($settings, 'attempttracking', $default, $selectyesno, true, '', true);

        block_smowl_settings_add_select($settings, 'floatblock', $default, $selectyesno, true);

        require_once($CFG->dirroot . '/blocks/smowl/classes/setting_height.php');

        $setting = new block_smowl_admin_setting_height('block_smowl/blockheight',
            get_string('blockheight', 'block_smowl'),
            get_string('blockheightdesc', 'block_smowl'),
            340, PARAM_INT);
        $setting->set_updatedcallback('block_smowl_settings_check_changes');
        $settings->add($setting);

    }
}

/**
 * Accessrule smowlcheckcam settings if is installed.
 * @param object $settings
 */
function block_smowl_settings_smowlcheckcam($settings) {
    global $CFG;

    $issetaccesrulecheckcam = file_exists($CFG->dirroot.'/mod/quiz/accessrule/smowlcheckcam/rule.php');
    if ($issetaccesrulecheckcam) {
        require_once($CFG->dirroot . '/blocks/smowl/classes/setting_accesrulesmowlcheckcam.php');

        block_smowl_settings_add_header($settings, 'accesrulesmowlcheckcamconfig', 'smowl_accesrule_smowlcheckcam');

        $selectyesno = [];
        $selectyesno[0] = get_string('no');
        $selectyesno[1] = get_string('yes');

        $default = 0;
        block_smowl_settings_add_select($settings, 'accesrulesmowlcheckcam', $default, $selectyesno, true);
    }
}

/**
 * Show entity Internal Settings.
 * @param object $settings
 */
function block_smowl_settings_internal($settings) {
    global $CFG, $PAGE;

    block_smowl_settings_add_header($settings, 'internalconfig', 'smowl_dev_heading', 'internalconfigdesc');
    block_smowl_settings_advanced_section($settings, 'viewsettingsinternal');

    if (get_config('block_smowl', 'viewsettingsinternal')) {
        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");

        // URLs.
        block_smowl_settings_add_header($settings, 'internalconfigurls', 'smowl_inturl_heading', 'internalconfigdesc');

        $default = block_smowl_connection::get_default_urlstudentview();
        block_smowl_settings_add_text($settings, 'urlstudentview', $default, PARAM_URL, true);

        // URLs API.
        block_smowl_settings_add_header($settings, 'internalconfigapiurls', 'smowl_intapiurl_heading', 'internalconfigdesc');

        $default = block_smowl_connection::get_default_urlsmowlapi();
        block_smowl_settings_add_text($settings, 'urlsmowlapi', $default, PARAM_URL, true);

        $default = block_smowl_connection::get_default_apilmssettings();
        block_smowl_settings_add_text($settings, 'apilmssettings', $default, PARAM_URL, true);

        $default = block_smowl_connection::get_default_apilmssettingscustomer();
        block_smowl_settings_add_text($settings, 'apilmssettingscustomer', $default, PARAM_URL, true);

        $default = block_smowl_connection::get_default_apiconfigclient();
        block_smowl_settings_add_text($settings, 'apiconfigclient', $default, PARAM_URL, true);

        $default = block_smowl_connection::get_default_apiaddactivity();
        block_smowl_settings_add_text($settings, 'apiaddactivity', $default, PARAM_URL, true);

        $default = block_smowl_connection::get_default_apimodifyactivity();
        block_smowl_settings_add_text($settings, 'apimodifyactivity', $default, PARAM_URL, true);

        // LTI Settings.
        block_smowl_settings_internal_lti($settings);
    }
}
/**
 * Show LTI URLs Internal Settings.
 * @param object $settings
 */
function block_smowl_settings_internal_lti($settings) {
    block_smowl_settings_add_header($settings, 'internalconfigltitool', 'smowl_intlti_heading', 'internalconfigdesc');

    $default = block_smowl_connection::get_default_urlsmowlltitool();
    block_smowl_settings_add_text($settings, 'urlsmowlltitool', $default, PARAM_URL, true);

    $default = block_smowl_connection::get_default_ltitoolinit();
    block_smowl_settings_add_text($settings, 'ltitoolinit', $default, PARAM_URL, true);

    $default = block_smowl_connection::get_default_ltitoolversion();
    block_smowl_settings_add_text($settings, 'ltitoolversion', $default, PARAM_RAW, true);

    $default = block_smowl_connection::get_default_ltitoolpublickeyset();
    block_smowl_settings_add_text($settings, 'ltitoolpublickeyset', $default, PARAM_URL, true);

    $default = block_smowl_connection::get_default_ltitoolinitiatelogin();
    block_smowl_settings_add_text($settings, 'ltitoolinitiatelogin', $default, PARAM_URL, true);

    $default = block_smowl_connection::get_default_ltitoolredirection();
    block_smowl_settings_add_text($settings, 'ltitoolredirection', $default, PARAM_URL, true);

    $default = block_smowl_connection::get_default_ltitoolconfigusage();
    block_smowl_settings_add_text($settings, 'ltitoolconfigusage', $default, PARAM_INT, true);

    $default = block_smowl_connection::get_default_ltitoollaunch();
    block_smowl_settings_add_text($settings, 'ltitoollaunch', $default, PARAM_INT, true);

    $default = block_smowl_connection::get_default_ltitoolconfigmemberships();
    block_smowl_settings_add_text($settings, 'ltitoolconfigmemberships', $default, PARAM_INT, true);

    $default = block_smowl_connection::get_default_urlsmowlltiapi();
    block_smowl_settings_add_text($settings, 'urlsmowlltiapi', $default, PARAM_URL, true);

    $default = block_smowl_connection::get_default_ltiapiapplications();
    block_smowl_settings_add_text($settings, 'ltiapiapplications', $default, PARAM_URL, true);

    $default = block_smowl_connection::get_default_ltiapideployments();
    block_smowl_settings_add_text($settings, 'ltiapideployments', $default, PARAM_URL, true);

    block_smowl_settings_internal_lti_config($settings);
}

/**
 * Show LTI Internal Settings.
 * @param object $settings
 */
function block_smowl_settings_internal_lti_config($settings) {
    block_smowl_settings_add_header($settings, 'internalconfiglticonfig', 'smowl_intlticonfig_heading', 'internalconfigdesc');

    $description = '';
    $default = '';
    // TODO.
    // Maybe admin_setting_configpasswordunmask.
    // Or add disabled="disabled" with check to use if SMOWL needs.

    block_smowl_settings_add_text($settings, 'ltientity', $default, PARAM_ALPHANUM, true, 'VOID');

    block_smowl_settings_add_text($settings, 'ltitypeid', $default, PARAM_ALPHANUM, true, 'VOID');

    block_smowl_settings_add_text($settings, 'ltideploymentid', $default, PARAM_RAW, true, 'VOID');

    block_smowl_settings_add_text($settings, 'ltiappid', $default, PARAM_RAW, true, 'VOID');

    block_smowl_settings_add_text($settings, 'ltirestid', $default, PARAM_ALPHANUM, true, 'VOID');
}