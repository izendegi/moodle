<?php

/**
 * Contains class for PayPal payment gateway.
 *
 * @package    paygw_mucommerce
 * @copyright  2023 Onenpro <info@onenpro.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_mucommerce;

class gateway extends \core_payment\gateway {
    public static function get_supported_currencies(): array {
        // See https://developer.paypal.com/docs/api/reference/currency-codes/,
        // 3-character ISO-4217: https://en.wikipedia.org/wiki/ISO_4217#Active_codes.
        return [
            'EUR'
        ];
    }

    /**
     * Configuration form for the gateway instance
     *
     * Use $form->get_mform() to access the \MoodleQuickForm instance
     *
     * @param \core_payment\form\account_gateway $form
     */
    public static function add_configuration_to_gateway_form(\core_payment\form\account_gateway $form): void {
        $mform = $form->get_mform();

        $mform->addElement('text', 'urlbase', get_string('urlbase', 'paygw_mucommerce'));
        $mform->setType('urlbase', PARAM_TEXT);
        $mform->addHelpButton('urlbase', 'urlbase', 'paygw_mucommerce');

        $mform->addElement('text', 'user', get_string('user', 'paygw_mucommerce'));
        $mform->setType('user', PARAM_TEXT);
        $mform->addHelpButton('user', 'user', 'paygw_mucommerce');

        $mform->addElement('password', 'pwd', get_string('pwd', 'paygw_mucommerce'));
        $mform->setType('pwd', PARAM_TEXT);
        $mform->addHelpButton('pwd', 'pwd', 'paygw_mucommerce');

        $options = [
            'live' => get_string('live', 'paygw_mucommerce'),
            'sandbox'  => get_string('sandbox', 'paygw_mucommerce'),
        ];

        $mform->addElement('select', 'environment', get_string('environment', 'paygw_mucommerce'), $options);
        $mform->addHelpButton('environment', 'environment', 'paygw_mucommerce');
    }

    /**
     * Validates the gateway configuration form.
     *
     * @param \core_payment\form\account_gateway $form
     * @param \stdClass $data
     * @param array $files
     * @param array $errors form errors (passed by reference)
     */
    public static function validate_gateway_form(\core_payment\form\account_gateway $form,
                                                 \stdClass $data, array $files, array &$errors): void {
        if ($data->enabled &&
            (empty($data->urlbase) || empty($data->user) || empty($data->pwd))) {
            $errors['enabled'] = get_string('gatewaycannotbeenabled', 'payment');
        }
    }
}
