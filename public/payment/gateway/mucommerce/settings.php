<?php

/**
 * Settings for the Mucommerce payment gateway
 *
 * @package    paygw_mucommerce
 * @copyright  2023 Onenpro <info@onenpro.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_heading('paygw_mucommerce_settings', '', get_string('pluginname_desc', 'paygw_mucommerce')));

    \core_payment\helper::add_common_gateway_settings($settings, 'paygw_mucommerce');
}
