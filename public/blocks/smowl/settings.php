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

if ($ADMIN->fulltree) {

    if (empty($_SERVER['REQUEST_URI'])) {
        return;
    }
    $uri = $_SERVER['REQUEST_URI'];

    if (!empty($uri) && strpos($uri, 'admin/settings.php?section=blocksettingsmowl') === false) {
        return;
    }

    require_once($CFG->dirroot . '/blocks/smowl/lib.php');
    require_once($CFG->dirroot . '/blocks/smowl/settingslib.php');
    $typeconfig = get_config('block_smowl', 'typeconfig');

    if (!$typeconfig || $typeconfig == 3) {
        if (!empty($uri) && !(strpos($uri, 'admin/settings.php?section=blocksettingsmowl') === false)) {
            set_config('typeconfig', null, 'block_smowl');
            block_smowl_settings_installation_type($settings);
        }
    }
    $institutionvalidated = get_config('block_smowl', 'institutionvalidated');
    if ($typeconfig == 1) {
        // Autoconfig.
        $clientid = get_config('block_smowl', 'clientid');
        $clientkey = get_config('block_smowl', 'clientkey');
        $clientvalidated = get_config('block_smowl', 'clientvalidated');
        $fromjson = block_smowl_get_client_config();
        if (strcasecmp($clientid, '') != 0 &&
            strcasecmp($clientkey, '') != 0) {
            // Client id and key defined.
            if (strcasecmp($clientvalidated, '') == 0) {
                // Show data to confirm.
                block_smowl_settings_client_connection($settings, $fromjson);
            } else {
                if (!block_smowl_check_entity()) {
                    // Is not defined entity, password, apikey or jwtsecret
                    $autoconfigerror = get_config('block_smowl', 'autoconfigerror');
                    if (strcasecmp($autoconfigerror, '') != 0) {
                        // Have error.
                        // Show data to confirm again.
                        block_smowl_settings_client_connection($settings);
                    } else {
                        // Not have error.
                        // Call to autoconfig.
                        if (block_smowl_api_send_client_config_request()) {
                            // If we config SMOWL correctly: we check LTI Tool and if need we create and send to SMOWL.
                            if (!block_smowl_lti_check()) {
                                // Create LTI tool.
                                block_smowl_lti_create_tool();
                                // After check LTI Tool.
                                block_smowl_api_send_lti_ws_config();
                            }
                            // After config LTI Tool we prepare and send info to lti api.
                            $smowlsettings = block_smowl_prepare_lms_settings();
                            block_smowl_send_lms_settings($smowlsettings);

                            // Redirect with the SMOWL config done message.
                            $message = get_string('autoconfigmessageconfigdone', 'block_smowl');
                            redirect($CFG->wwwroot.'/?redirect=0', $message, null, \core\output\notification::NOTIFY_INFO);
                        }
                        redirect($CFG->wwwroot.'/admin/settings.php?section=blocksettingsmowl');
                    }
                    return;
                } else {
                    // Validate lti.
                    if (!block_smowl_lti_check()) {
                        block_smowl_lti_create_tool();
                        block_smowl_api_send_lti_ws_config();
         
                    }

                    // Show all the configurations.
                    block_smowl_clear_preview_config();
                    block_smowl_settings_entity_connection($settings);
                    block_smowl_settings_instructors($settings);
                    block_smowl_settings_smowlcheckcam($settings);
                    block_smowl_settings_internal($settings);
                    $smowlsettings = block_smowl_prepare_lms_settings();
                    block_smowl_send_lms_settings($smowlsettings);
                }
            }
        } else if ($fromjson && isset($fromjson->client_id) && isset($fromjson->client_key)) {
            set_config('clientid', $fromjson->client_id, 'block_smowl');
            set_config('clientkey', $fromjson->client_key, 'block_smowl');
            block_smowl_settings_client_connection($settings, $fromjson);
        } else {
            set_config('clientid', null, 'block_smowl');
            set_config('clientkey', null, 'block_smowl');
            set_config('autoconfigerror', 'clientvoid', 'block_smowl');
            block_smowl_settings_client_connection($settings, $fromjson);
        }

    } else if ($typeconfig == 2) {
        $checkentity = block_smowl_check_entity();
        if ($checkentity && !block_smowl_lti_check()) {
            block_smowl_lti_create_tool();
            block_smowl_api_send_lti_ws_config();
            // After config LTI Tool we prepare and send info to lti api.
        }
        block_smowl_settings_entity_connection($settings);
        if ($checkentity) {            
            $smowlsettings = block_smowl_prepare_lms_settings();
            block_smowl_send_lms_settings($smowlsettings);
            block_smowl_settings_instructors($settings);
            block_smowl_settings_smowlcheckcam($settings);
            block_smowl_settings_internal($settings);
        }
    }
}
