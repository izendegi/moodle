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
 * External library
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   block_user_favorites
 * @copyright 24/02/2023 LdesignMedia.nl - Luuk Verhoeven
 * @author    Hamza Tamyachte
 **/

namespace block_user_favorites;

use block_user_favorites\output\output_favorites;
use context_block;
use context_user;
use dml_exception;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use moodle_exception;
use required_capability_exception;

/**
 * Class external.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   block_user_favorites
 * @copyright 24/02/2023 LdesignMedia.nl - Luuk Verhoeven
 * @author    Hamza Tamyachte
 **/
class external extends external_api {

    /**
     * If everything goes according plan, we can use this code.
     * RESPONSE_CODE_SUCCESS
     */
    public const RESPONSE_CODE_SUCCESS = 200;

    /**
     * Set order.
     *
     * @param string $hash
     * @param int $sortorder
     *
     * @return array
     *
     * @throws dml_exception
     */
    public static function set_order(string $hash, int $sortorder): array {
        global $USER;

        require_capability('block/user_favorites:edit', context_user::instance($USER->id), $USER);

        // Parameter validation.
        $params = self::validate_parameters(self::set_order_parameters(), ['hash' => $hash, 'sortorder' => $sortorder]);

        $favorites = new favorites();
        $favorites->set_order($params['hash'], $params['sortorder']);

        return [
            'result_code' => self::RESPONSE_CODE_SUCCESS,
        ];
    }

    /**
     * Describes the input parameters for set_order.
     *
     * @return external_function_parameters
     */
    public static function set_order_parameters(): external_function_parameters {
        return new external_function_parameters (
            [
                'hash' => new external_value(PARAM_TEXT, 'URL HASH', VALUE_REQUIRED),
                'sortorder' => new external_value(PARAM_INT, 'The sortorder of the block', VALUE_REQUIRED),
            ]
        );

    }

    /**
     * Set url return value.
     *
     * @return external_single_structure
     */
    public static function set_order_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result_code' => new external_value(PARAM_INT, 'The response code', VALUE_REQUIRED),
            ]);
    }

    /**
     * Set a url.
     *
     * @param string $hash
     * @param string $title
     * @param int $blockid
     * @param array $optional
     *
     * @return array
     * @throws dml_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     */
    public static function set_url(string $hash, string $title, int $blockid, array $optional): array {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::set_url_parameters(),
            ['hash' => $hash, 'title' => $title, 'blockid' => $blockid, 'optional' => $optional]);

        require_capability('block/user_favorites:add', context_block::instance($params['blockid']), $USER);
        $favorites = new favorites();
        if (!empty($params['optional']['url'])) {

            if (!filter_var($params['optional']['url'], FILTER_VALIDATE_URL)) {
                throw new moodle_exception('Incorrect url.');
            }

            $favorites->set_by_url($params['optional']['url'], $params['title']);

            return [
                'result_code' => self::RESPONSE_CODE_SUCCESS,
            ];
        }

        // Update url title.
        $favorites->set_title($params['hash'], $params['title']);

        return [
            'result_code' => self::RESPONSE_CODE_SUCCESS,
        ];
    }

    /**
     * Describes the input parameters for set_url.
     *
     * @return external_function_parameters
     */
    public static function set_url_parameters(): external_function_parameters {
        return new external_function_parameters (
            [
                'hash' => new external_value(PARAM_TEXT, 'URL HASH', VALUE_REQUIRED),
                'title' => new external_value(PARAM_RAW, 'The title of the url', VALUE_REQUIRED),
                'blockid' => new external_value(PARAM_INT, 'The ID of the block', VALUE_REQUIRED),
                'optional' => new external_single_structure(
                    [
                        'url' => new external_value(PARAM_URL, 'URL', VALUE_OPTIONAL),
                    ]
                ),
            ]
        );
    }

    /**
     * Set url return value.
     *
     * @return external_single_structure
     */
    public static function set_url_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result_code' => new external_value(PARAM_INT, 'The response code', VALUE_REQUIRED),
            ]);
    }

    /**
     * Delete url
     *
     * @param string $hash
     * @param int $blockid
     *
     * @return array
     * @throws required_capability_exception
     * @throws dml_exception
     */
    public static function delete_url(string $hash, int $blockid): array {
        global $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::delete_url_parameters(), ['hash' => $hash, 'blockid' => $blockid]);

        require_capability('block/user_favorites:delete', context_block::instance($params['blockid']), $USER);

        $favorites = new favorites();
        $favorites->delete_by_hash($params['hash']);

        return [
            'result_code' => self::RESPONSE_CODE_SUCCESS,
        ];
    }

    /**
     * Describes the input parameters for delete_url.
     *
     * @return external_function_parameters
     */
    public static function delete_url_parameters(): external_function_parameters {
        return new external_function_parameters (
            [
                'hash' => new external_value(PARAM_TEXT, 'URL HASH', VALUE_REQUIRED),
                'blockid' => new external_value(PARAM_INT, 'The ID of the block', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Delete url return value.
     *
     * @return external_single_structure
     */
    public static function delete_url_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result_code' => new external_value(PARAM_INT, 'The response code', VALUE_REQUIRED),
            ]);
    }

    /**
     * Get block content
     *
     * @param string $url
     * @param int $blockid
     *
     * @return array
     * @throws required_capability_exception
     */
    public static function get_content(string $url, int $blockid): array {
        global $PAGE, $USER;

        // Parameter validation.
        $params = self::validate_parameters(self::get_content_parameters(), ['url' => $url, 'blockid' => $blockid]);

        $context = context_block::instance($params['blockid']);
        require_capability('block/user_favorites:view', $context, $USER);

        $favorites = new favorites();
        $PAGE->set_context($context);
        $renderer = $PAGE->get_renderer('block_user_favorites');

        return [
            'content' => $renderer->render_favorites(new output_favorites($favorites, $params['url'])),
            'result_code' => self::RESPONSE_CODE_SUCCESS,
        ];
    }

    /**
     * Describes the input parameters for get_content.
     *
     * @return external_function_parameters
     */
    public static function get_content_parameters(): external_function_parameters {
        return new external_function_parameters (
            [
                'url' => new external_value(PARAM_URL, 'The current url', VALUE_REQUIRED),
                'blockid' => new external_value(PARAM_INT, 'The ID of the block', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Content return value.
     *
     * @return external_single_structure
     */
    public static function get_content_returns(): external_single_structure {
        return new external_single_structure(
            [
                'result_code' => new external_value(PARAM_INT, 'The response code', VALUE_REQUIRED),
                'content' => new external_value(PARAM_RAW, 'The block content', VALUE_REQUIRED),
            ]);
    }

}
