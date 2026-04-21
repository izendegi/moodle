<?php

/**
 * External functions and service definitions for the Mucommerce payment gateway plugin.
 *
 * @package    paygw_mucommerce
 * @copyright  2023 Onenpro <info@onenpro.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'paygw_mucommerce_mark_transaction_complete' => [
        'classname'   => 'paygw_mucommerce\external\mark_transaction_complete',
        'classpath'   => '',
        'description' => 'When a mucommerce transaction comes back as complete here we make that user can enrol in paid course.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => false,
    ],
    'paygw_mucommerce_send_payment_req' => [
        'classname'   => 'paygw_mucommerce\external\send_payment_req',
        'classpath'   => '',
        'description' => 'Send payment request to mucommerce platform.',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
    ],
    'paygw_mucommerce_check_payment_is_made' => [
        'classname'   => 'paygw_mucommerce\external\check_payment_is_made',
        'classpath'   => '',
        'description' => 'Check if user had paid for the course.',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => false,
    ],
    'paygw_mucommerce_save_user_data' => [
        'classname'   => 'paygw_mucommerce\external\save_user_data',
        'classpath'   => '',
        'description' => 'Save the billing data of a user before make a payment.',
        'type'        => 'write',
        'ajax'        => true,
        'loginrequired' => true,
    ],
    'paygw_mucommerce_get_billing_data_form' => [
        'classname'   => 'paygw_mucommerce\external\edit_billing_data_form',
        'classpath'   => '',
        'description' => 'Get the billing data form to allow user to edit her information before make the payment.',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => true,
    ],
];

$services = [
    'Mucommerce payment gateway external web services' => [
        'functions'   => [
            'paygw_mucommerce_mark_transaction_complete'
        ],
        'restrictedusers'   => 0,
        'enabled' => 1,
        'shortname' => 'paygw_mucommerce_external_ws'
    ],
];