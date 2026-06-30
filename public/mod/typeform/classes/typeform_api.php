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
 * Typeform API integration class
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_typeform;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for interacting with Typeform API
 */
class typeform_api {
    /**
     * Get Typeform API token from config
     *
     * @return string|null
     * @throws \dml_exception
     */
    public static function get_api_token() {
        return get_config('typeform', 'apitoken');
    }

    /**
     * Get Typeform API url from config
     *
     * @return string|null
     * @throws \dml_exception
     */
    public static function get_api_url() {
        return get_config('typeform', 'apiurl');
    }

    /**
     * Get allowed workspaces from config
     *
     * @return array Array of workspace IDs (trimmed and filtered)
     * @throws \dml_exception
     */
    public static function get_allowed_workspaces() {
        $workspaces = get_config('typeform', 'allowedworkspaces');
        if (!empty($workspaces)) {
            // Split by comma, trim each ID, and filter out empty values
            $workspaceids = explode(',', $workspaces);
            $ids = [];
            foreach ($workspaceids as $workspaceid) {
                $ids[] = [
                        'id' => $workspaceid,
                        'name' => self::get_workspacename($workspaceid),
                ];
            }
            return $ids;
        }

        return self::get_all_workspaces();
    }

    /**
     * Get all workspaces from typeform
     *
     * @return array Array of workspace IDs (trimmed and filtered)
     * @throws \dml_exception
     */
    public static function get_all_workspaces() {
        $endpoint = 'workspaces';
        $response = self::api_request($endpoint);
        $workspaces = [];

        if (!$response) {
            return $workspaces;
        }
        if (array_key_exists('items', $response)) {
            foreach ($response['items'] as $item) {
                $workspaces[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                ];
            }
        }
        return $workspaces;
    }

    /**
     * Make API request to Typeform
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $params Request parameters
     * @return array|false Response data or false on error
     * @throws \dml_exception
     */
    public static function api_request($endpoint, $method = 'GET', $params = []) {
        $token = self::get_api_token();
        if (empty($token)) {
            return false;
        }
        $url = self::get_api_url();
        $url .= ltrim($endpoint, '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlerror = curl_error($ch);
        curl_close($ch);

        if ($curlerror) {
            debugging('Typeform API curl error: ' . $curlerror, DEBUG_DEVELOPER);
            return false;
        }

        if ($httpcode >= 200 && $httpcode < 300) {
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                debugging('Typeform API JSON decode error: ' . json_last_error_msg(), DEBUG_DEVELOPER);
                return false;
            }
            return $data;
        }

        debugging('Typeform API HTTP error: ' . $httpcode, DEBUG_DEVELOPER);
        return false;
    }

    /**
     * get_workspacename
     * @param $workspaceid
     * @return mixed|string
     * @throws \dml_exception
     */
    public static function get_workspacename($workspaceid) {
        if (empty($workspaceid)) {
            return '';
        }
        $response = self::api_request('/workspaces/' . $workspaceid);
        if (is_array($response) && array_key_exists('name', $response)) {
            return $response['name'];
        }
        return '';
    }

    /**
     * Get list of forms from Typeform for a specific workspace (or all if no workspace specified)
     *
     * @param null $workspaceid Workspace ID (optional)
     * @param int $page Page number
     * @param int $pagesize Page size
     * @return array|false List of forms and pagination info, or false on error
     * @throws \dml_exception
     */
    public static function get_forms($workspaceid = null, $workspacename = '', $page = 1, $pagesize = 100) {
        // Build endpoint with optional workspace_id parameter
        $endpoint = 'forms?page_size=' . $pagesize . '&page=' . $page;
        if (!empty($workspaceid)) {
            $endpoint .= '&workspace_id=' . urlencode(trim($workspaceid));
        }

        $response = self::api_request($endpoint);
        if (!$response) {
            return false;
        }

        // Get workspace name.
        if (empty($workspacename) && !is_null($workspaceid)) {
            $workspacename = self::get_workspacename($workspaceid);
        }
        // Handle both v1 and v2 API response formats
        if (isset($response['items'])) {
            // API v2 format
            $items = $response['items'];
            $pagecount = isset($response['page_count']) ? $response['page_count'] : 1;
            $itemcount = isset($response['total_items']) ? $response['total_items'] : count($items);
        } else if (isset($response['forms'])) {
            // API v1 format
            $items = $response['forms'];
            $pagecount = isset($response['page_count']) ? $response['page_count'] : 1;
            $itemcount = isset($response['total_items']) ? $response['total_items'] : count($items);
        } else {
            return false;
        }

        $forms = [];
        foreach ($items as $form) {
            $formid = isset($form['id']) ? $form['id'] : (isset($form['form_id']) ? $form['form_id'] : '');
            $formtitle = isset($form['title']) ? $form['title'] : (isset($form['name']) ? $form['name'] : '');
            if (!empty($formid)) {
                $forms[] = [
                    'id' => $formid,
                    'title' => $formtitle,
                    'workspace' => $workspacename,
                ];
            }
        }

        return [
            'forms' => $forms,
            'page' => $page,
            'page_count' => $pagecount,
            'total_items' => $itemcount,
            'has_more' => $page < $pagecount,
        ];
    }

    /**
     * Get all forms from Typeform, handling pagination and multiple workspaces
     * If workspaces are configured, makes one call per workspace
     * If no workspaces configured, makes a single call without filtering
     *
     * @return array|false List of all forms or false on error
     * @throws \dml_exception
     */
    public static function get_all_forms() {
        $allowedworkspaces = self::get_allowed_workspaces();
        $allforms = [];

        if (empty($allowedworkspaces)) {
            return [];
        }
        // If workspaces are configured, make one call per workspace
        foreach ($allowedworkspaces as $workspace) {
            // Get all pages for this workspace
            $page = 1;
            $hasmore = true;

            while ($hasmore) {
                $result = self::get_forms($workspace['id'], $workspace['name'], $page, 100);

                if ($result === false) {
                    // Error on this workspace, continue with next
                    break;
                }

                if (!empty($result['forms'])) {
                    $allforms = array_merge($allforms, $result['forms']);
                }

                $hasmore = $result['has_more'];
                $page++;
            }
        }

        // Remove duplicates based on form ID (in case a form appears in multiple workspaces)
        $uniqueforms = [];
        $seenids = [];
        foreach ($allforms as $form) {
            if (!in_array($form['id'], $seenids)) {
                $uniqueforms[] = $form;
                $seenids[] = $form['id'];
            }
        }

        // Return empty array instead of false if no forms found
        return $uniqueforms;
    }

    /**
     * Get a specific form by ID
     *
     * @param string $formid Form ID
     * @return array|false Form data with 'id' and 'title' keys, or false on error
     * @throws \dml_exception
     */
    public static function get_form($formid) {
        $endpoint = 'forms/' . $formid;
        $response = self::api_request($endpoint);

        if (!$response) {
            return false;
        }

        // Extract title from response (handle different API response formats)
        $title = '';
        if (isset($response['title'])) {
            $title = $response['title'];
        } else if (isset($response['name'])) {
            $title = $response['name'];
        }

        return [
            'id' => $formid,
            'title' => $title,
        ];
    }

    /**
     * Test API connection
     *
     * @return bool True if connection successful
     * @throws \dml_exception
     */
    public static function test_connection() {
        $response = self::api_request('forms?page_size=1');
        return $response !== false;
    }
}
