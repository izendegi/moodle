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
 * Block Smowl connections.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_smowl_connection {

    /**
     * Get URL student view.
     * @return string URL config
     */
    public static function get_urlstudentview() {
        return get_config('block_smowl', 'urlstudentview');
    }

    /**
     * Get default URL student view.
     * @return string URL default
     */
    public static function get_default_urlstudentview() {
        return 'https://swl.smowltech.net/monitor/';
    }

    // API URL and functions.
    /**
     * Get URL smowl API.
     * @return string URL config
     */
    public static function get_urlsmowlapi() {
        return get_config('block_smowl', 'urlsmowlapi');
    }

    /**
     * Get default URL smowl API.
     * @return string URL default
     */
    public static function get_default_urlsmowlapi() {
        return 'https://results-api.smowltech.net/index.php/';
    }

    /**
     * Get URL API lms settings.
     * @return string URL config
     */
    public static function get_apilmssettings() {
        return get_config('block_smowl', 'apilmssettings');
    }

    /**
     * Get default URL API lms settings.
     * @return string URL default
     */
    public static function get_default_apilmssettings() {
        return 'V2/configs/Lmsconfiguration/set';
    }

    /**
     * Get URL API lms settings.
     * @return string URL config
     */
    public static function get_apilmssettingscustomer() {
        return get_config('block_smowl', 'apilmssettingscustomer');
    }

    /**
     * Get default URL API lms settings.
     * @return string URL default
     */
    public static function get_default_apilmssettingscustomer() {
        return 'Institution/Plugin/Lmsconfigurationrequest/set';
    }

    /**
     * Get api addactivity
     * @return string URL config
     */
    public static function get_apiaddactivity() {
        return get_config('block_smowl', 'apiaddactivity');
    }

    /**
     * Get default add activity.
     * @return string URL default
     */
    public static function get_default_apiaddactivity() {
        return 'V2/configs/activeServices/addActivity';
    }

    /**
     * Get api modify activity
     * @return string URL config
     */
    public static function get_apimodifyactivity() {
        return get_config('block_smowl', 'apimodifyactivity');
    }

    /**
     * Get default modify activity
     * @return string URL default
     */
    public static function get_default_apimodifyactivity() {
        return 'V2/configs/activeServices/modifyActivity';
    }

    /**
     * Get API config client.
     * @return string API config client in config
     */
    public static function get_apiconfigclient() {
        return get_config('block_smowl', 'apiconfigclient');
    }

    /**
     * Get default API config client.
     * @return string API config default
     */
    public static function get_default_apiconfigclient() {
        return 'Institution/Plugin/Activation/get';
    }

    /**
     * This function prepare object and call send function to Smowl API.
     * @param object $lmssettings with moodle SMOWL settings and others.
     */
    public static function send_lms_settings($lmssettings) {
        $lmssettings = json_encode($lmssettings);

        $apilink = self::get_urlsmowlapi();
        $apifunction = self::get_apilmssettings();
        $url = $apilink.$apifunction;
        $return = self::smowl_api_put($lmssettings, $url);

        // API called event.
        $event = \block_smowl\event\api_called::create();
        $event->trigger();

        return $return;
    }

    /**
     * This function send information to Smowl API by PUT.
     * @param string $putjson with data to send.
     * @param string $url with url to send.
     */
    public static function smowl_api_put($putjson, $url) {
        global $CFG;
        if (!block_smowl_check_entity()) {
            return;
        }

        $arrayparams = ['Lmsconfiguration' => $putjson];

        $postparams = http_build_query($arrayparams, '', '&');

        $username = get_config('block_smowl', 'entity');
        $password = get_config('block_smowl', 'apikey');
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        // Create new cURL.
        $curl = new curl();
        // Add options.
        $options = [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_USERPWD' => $username . ":" . $password,
            'CURLOPT_POST' => 1,
            'CURLOPT_IPRESOLVE' => CURL_IPRESOLVE_V4,
            'CURLOPT_USERAGENT' => $agent,
        ];
        //Added proxy support
        if ($CFG->proxyhost) {
            $options['CURLOPT_PROXY'] = $CFG->proxyhost;
            $options['CURLOPT_PROXYPORT'] = $CFG->proxyport;
            if ($CFG->proxyuser && $CFG->proxypassword) {
                $options['CURLOPT_PROXYUSERPWD'] = $CFG->proxyuser.':'.$CFG->proxypassword;
            }
        }
        $content = $curl->post($url, $postparams, $options);

        if ($content === "true") {
            return true;
        }
        return false;
    }

    /**
     * This function send information to config client Smowl API by PUT.
     * @param string $lmsurl to send.
     * @return object JSON API config returned.
     */
    public static function request_config_client($lmsurl) {
        global $CFG;
        $apilink = self::get_urlsmowlapi();
        $apifunction = self::get_apiconfigclient();
        $url = $apilink.$apifunction;

        $arrayparams = [];
        $arrayparams["lmsid"] = get_config('block_smowl', 'lmsid');
        $arrayparams["lmsurl"] = $lmsurl;

        $postparams = http_build_query($arrayparams, '', '&');

        $username = get_config('block_smowl', 'clientid');
        $password = get_config('block_smowl', 'clientkey');
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        // Create new cURL.
        $curl = new curl();
        // Add the options.
        $options = [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_USERPWD' => $username . ":" . $password,
            'CURLOPT_POST' => 1,
            'CURLOPT_IPRESOLVE' => CURL_IPRESOLVE_V4,
            'CURLOPT_USERAGENT' => $agent,
        ];
        //Added proxy support
        if ($CFG->proxyhost) {
            $options['CURLOPT_PROXY'] = $CFG->proxyhost;
            $options['CURLOPT_PROXYPORT'] = $CFG->proxyport;
            if ($CFG->proxyuser && $CFG->proxypassword) {
                $options['CURLOPT_PROXYUSERPWD'] = $CFG->proxyuser.':'.$CFG->proxypassword;
            }
        }

        $content = $curl->post($url, $postparams, $options);

        $jsondata = json_decode($content);
        if (!$jsondata) {
            set_config('autoconfigerror', $content, 'block_smowl');
            return false;
        }
        return $jsondata;
    }

    /**
     * This function prepare object and call send function to Smowl API.
     * @param object $lmssettings with moodle SMOWL settings and others.
     */
    public static function send_lms_customer_settings($lmssettings) {
        $lmssettings = json_encode($lmssettings);

        $apilink = self::get_urlsmowlapi();
        $apifunction = self::get_apilmssettingscustomer();
        $url = $apilink.$apifunction;

        $return = self::customer_api_put($lmssettings, $url);

        // API called event.
        $event = \block_smowl\event\api_called::create();
        $event->trigger();

        return $return;
    }

    /**
     * This function send information to config client Smowl API Customer by PUT.
     * @param string $putjson with data to send.
     * @param string $url with url to send.
     * @return object JSON API config returned.
     */
    public static function customer_api_put($putjson, $url) {
        global $CFG;
        $entity = get_config('block_smowl', 'entity');
        $arrayparams = [
            'Lmsconfiguration' => $putjson,
            'entity_Name' => $entity,
        ];

        $postparams = http_build_query($arrayparams, '', '&');

        $username = get_config('block_smowl', 'clientid');
        $password = get_config('block_smowl', 'clientkey');
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        // Create new cURL.
        $curl = new curl();
        // Add options.
        $options = [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_USERPWD' => $username . ":" . $password,
            'CURLOPT_POST' => 1,
            'CURLOPT_IPRESOLVE' => CURL_IPRESOLVE_V4,
            'CURLOPT_USERAGENT' => $agent,
        ];
        //Added proxy support
        if ($CFG->proxyhost) {
            $options['CURLOPT_PROXY'] = $CFG->proxyhost;
            $options['CURLOPT_PROXYPORT'] = $CFG->proxyport;
            if ($CFG->proxyuser && $CFG->proxypassword) {
                $options['CURLOPT_PROXYUSERPWD'] = $CFG->proxyuser.':'.$CFG->proxypassword;
            }
        }

        $content = $curl->post($url, $postparams, $options);

        if ($content === "true") {
            return true;
        }
        return false;
    }

    // LTI Config and functions.

    /**
     * Get URL smowl LTI.
     * @return string URL config
     */
    public static function get_urlsmowlltitool() {
        return get_config('block_smowl', 'urlsmowlltitool');
    }

    /**
     * Get default URL smowl.
     * @return string URL default
     */
    public static function get_default_urlsmowlltitool() {
        global $CFG;
        return 'https://lti-smowl-global.smowltech.net/lti/';
    }

    /**
     * Get smowl config LTI  version.
     * @return string version
     */
    public static function get_ltitoolversion() {
        return get_config('block_smowl', 'ltitoolversion');
    }

    /**
     * Get default config LTI version.
     * @return string version
     */
    public static function get_default_ltitoolversion() {
        return '1.3.0';
    }

    /**
     * Get smowl config LTI init.
     * @return string init
     */
    public static function get_ltitoolinit() {
        return get_config('block_smowl', 'ltitoolinit');
    }

    /**
     * Get default config LTI init.
     * @return string init
     */
    public static function get_default_ltitoolinit() {
        return 'launch';
    }

    /**
     * Get smowl config LTI keytype.
     * @return string URL config
     */
    public static function get_ltitoolkeytype() {
        return get_config('block_smowl', 'ltitoolkeytype');
    }

    /**
     * Get default  config LTI keytype.
     * @return string keytype
     */
    public static function get_default_ltitoolkeytype() {
        return 'JWK_KEYSET'; // Default.
    }

    /**
     * Get smowl config LTI publickeyset.
     * @return string publickeyset
     */
    public static function get_ltitoolpublickeyset() {
        return get_config('block_smowl', 'ltitoolpublickeyset');
    }

    /**
     * Get default config LTI publickeyset.
     * @return string publickeyset
     */
    public static function get_default_ltitoolpublickeyset() {
        return 'jkws';
    }

    /**
     * Get smowl config LTI initiatelogin.
     * @return string initiatelogin
     */
    public static function get_ltitoolinitiatelogin() {
        return get_config('block_smowl', 'ltitoolinitiatelogin');
    }

    /**
     * Get default config LTI initiatelogin.
     * @return string initiatelogin
     */
    public static function get_default_ltitoolinitiatelogin() {
        return 'login';
    }

    /**
     * Get smowl config LTI redirection.
     * @return string redirection
     */
    public static function get_ltitoolredirection() {
        return get_config('block_smowl', 'ltitoolredirection');
    }

    /**
     * Get default config LTI redirection.
     * @return string redirection
     */
    public static function get_default_ltitoolredirection() {
        return 'launch';
    }

    /**
     * Get smowl config LTI configusage.
     * @return int configusage
     */
    public static function get_ltitoolconfigusage() {
        return get_config('block_smowl', 'ltitoolconfigusage');
    }

    /**
     * Get default config LTI configusage.
     * @return int configusage
     */
    public static function get_default_ltitoolconfigusage() {
        return 2;
    }

    /**
     * Get smowl config LTI launch.
     * @return int launch
     */
    public static function get_ltitoollaunch() {
        return get_config('block_smowl', 'ltitoollaunch');
    }

    /**
     * Get default config LTI launch.
     * @return int launch
     */
    public static function get_default_ltitoollaunch() {
        return 3; // Embed, without blocks.
    }

    /**
     * Get LTI tool data for configmemberships.
     * @return string URL config
     */
    public static function get_ltitoolconfigmemberships() {
        return get_config('block_smowl', 'ltitoolconfigmemberships');
    }

    /**
     * Get default LTI tool data for configmemberships.
     * @return string URL default
     */
    public static function get_default_ltitoolconfigmemberships() {
        return 1;
    }

    // LTI API URL and functions.
    /**
     * Get URL smowl LTI API.
     * @return string URL config
     */
    public static function get_urlsmowlltiapi() {
        return get_config('block_smowl', 'urlsmowlltiapi');
    }

    /**
     * Get default URL smowl LTI API.
     * @return string URL default
     */
    public static function get_default_urlsmowlltiapi() {
        global $CFG;
        return 'https://lti-smowl-global.smowltech.net/api/';
    }

    /**
     * Get URL API ltiapplications.
     * @return string URL config
     */
    public static function get_ltiapiapplications() {
        return get_config('block_smowl', 'ltiapiapplications');
    }

    /**
     * Get default URL API lms settings.
     * @return string URL default
     */
    public static function get_default_ltiapiapplications() {
        return 'v1/moodle/lti-applications';
    }

    /**
     * Get URL API ltiapplications.
     * @return string URL config
     */
    public static function get_ltiapideployments() {
        return get_config('block_smowl', 'ltiapideployments');
    }

    /**
     * Get default URL API lms settings.
     * @return string URL default
     */
    public static function get_default_ltiapideployments() {
        return 'v1/moodle/deployments'; // Añadir /{id}.
    }


    /**
     * This function send information to config client Smowl API by PUT.
     * @param object $ltitype object withs LTI info.
     * @param string $host URL from call.
     * @param string $typecall GET or others.
     * @return object JSON API config returned.
     */
    public static function send_lti_config($ltitype, $host, $typecall = '' ) {
        global $CFG;

        $apilink = self::get_urlsmowlltiapi();
        $apifunction = self::get_ltiapiapplications();
        $url = $apilink.$apifunction;

        $curlcall = '';
        if ($typecall != 'GET') {
            $arrayparams = [];
            $arrayparams["name"] = $ltitype->name;
            $arrayparams["client_id"] = $ltitype->clientid;
            $arrayparams["auth_login_url"] = $host . "/mod/lti/auth.php";
            $arrayparams["auth_token_url"] = $host . "/mod/lti/token.php";
            $arrayparams["key_set_url"] = $host . "/mod/lti/certs.php";
            $arrayparams["issuer"] = $host;
            $arrayparams["host"] = $host;
            $arrayparams["deployment_id"] = $ltitype->id;
            $curlcall = format_postdata_for_curlcall($arrayparams);
        }
        if ($typecall == 'PUT') {
            $url .= '/' . get_config('block_smowl', 'ltiappid');
        }
        $username = get_config('block_smowl', 'entity');
        $password = get_config('block_smowl', 'apikey');
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        // Create new cURL.
        $curl = new curl();
        // Add options.
        $options = [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_USERPWD' => $username . ":" . $password,
            'CURLOPT_POST' => 1,
            'CURLOPT_IPRESOLVE' => CURL_IPRESOLVE_V4,
            'CURLOPT_USERAGENT' => $agent,
        ];
        if ($typecall) {
            $options['CURLOPT_CUSTOMREQUEST'] = $typecall;
        }
        //Added proxy support
        if ($CFG->proxyhost) {
            $options['CURLOPT_PROXY'] = $CFG->proxyhost;
            $options['CURLOPT_PROXYPORT'] = $CFG->proxyport;
            if ($CFG->proxyuser && $CFG->proxypassword) {
                $options['CURLOPT_PROXYUSERPWD'] = $CFG->proxyuser.':'.$CFG->proxypassword;
            }
        }

        $content = $curl->post($url, $curlcall, $options);
        $errorno = $curl->get_errno();
        $info = $curl->get_info();

        $jsondata = json_decode($content);

        if (!$jsondata) {
            $message = get_string('ltisendtoolerror', 'block_smowl');
            if (isset($info['http_code'])) {
                $message .= ': ' . $info['http_code'];
                if ($info['http_code'] == 401) {
                    $message = get_string('ltisendtoolneedactivation', 'block_smowl');
                }
            }
            set_config('lticonfigmessage', $message , 'block_smowl');
            return false;
        }
        return $jsondata;
    }

    /**
     * This function send information to config client Smowl API by PUT.
     * @param string $token to send.
     * @param string $arrayparams params to send.
     * @param string $host to send.
     * @return object JSON API config returned.
     */
    public static function send_ws_config($token, $arrayparams, $host) {
        global $CFG;
        $apilink = self::get_urlsmowlltiapi();
        $apideployments = self::get_ltiapideployments();
        $url = $apilink . $apideployments . '/' . get_config('block_smowl', 'ltideploymentid');

        $username = get_config('block_smowl', 'entity');
        $password = get_config('block_smowl', 'apikey');

        $rest = [];
        $rest["url"] = $host . "/webservice/rest/server.php";
        $rest["token"] = $token;
        $arrayparams["moodle_rest_configuration"] = $rest;

        $curlcall = format_postdata_for_curlcall($arrayparams);
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        // Create new cURL.
        $curl = new curl();
        // Add options.
        $options = [
            'CURLOPT_CUSTOMREQUEST' => 'PATCH',
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_USERPWD' => $username . ":" . $password,
            'CURLOPT_POST' => 1,
            'CURLOPT_IPRESOLVE' => CURL_IPRESOLVE_V4,
            'CURLOPT_USERAGENT' => $agent,
        ];
        //Added proxy support
        if ($CFG->proxyhost) {
            $options['CURLOPT_PROXY'] = $CFG->proxyhost;
            $options['CURLOPT_PROXYPORT'] = $CFG->proxyport;
            if ($CFG->proxyuser && $CFG->proxypassword) {
                $options['CURLOPT_PROXYUSERPWD'] = $CFG->proxyuser.':'.$CFG->proxypassword;
            }
        }

        $content = $curl->post($url, $curlcall, $options);
        $jsondata = json_decode($content);

        if (!$jsondata) {
            $message = get_string('ltisendwserror', 'block_smowl');
            $info = $curl->get_info();
            if (isset($info['http_code'])) {
                $message .= ': ' . $info['http_code'];
            }
            set_config('lticonfigmessage', $message , 'block_smowl');
            return false;
        }
        return $jsondata;
    }
}