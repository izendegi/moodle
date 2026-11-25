<?php

/**
 * This class contains a list of webservice functions related to the Mucommerce payment gateway.
 *
 * @package    paygw_mucommerce
 * @copyright  2023 Onenpro <info@onenpro.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace paygw_mucommerce\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_payment\helper;
use core_payment\helper as payment_helper;
use paygw_mucommerce\mucommerce_helper;
use paygw_mucommerce\mucommerce_enrolment_checker;
use paygw_mucommerce\UserFields;
use paygw_mucommerce\form\userdataform;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/config.php');

require_once($CFG->libdir . '/externallib.php');

class edit_billing_data_form extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([]);
    }
    
    /**
     * Perform what needs to be done when a transaction is reported to be complete.
     * This function does not take cost as a parameter as we cannot rely on any provided value.
     *
     * @return array
     */
    public static function execute(): array {
//         global $USER;
        
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:editownprofile', $context);
        
        $message = get_string('editbillingdataformcannotget', 'paygw_mucommerce');
        $success = false;
        $form = '';
        
        try {
            $userdataform = new userdataform();
            
            if(!is_null($userdataform)) {
                $form = $userdataform->render();
                if(is_null($form)) {
                    $message = get_string('editbillingdataformcannotrender', 'paygw_mucommerce');
                } else {
                    $success = true;
                    $message = '';
                }
            }
        } catch (\Exception $e) {
            debugging('Exception in edit_billing_data_form: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $message = get_string('internalerroreditbillingdataform', 'paygw_mucommerce');
        }
        
        return [
            'success' => $success,
            'message' => $message,
            'form' => $form
        ];
    }
    
    /**
     * Returns description of method result value.
     *
     * @return external_function_parameters
     */
    public static function execute_returns() {
        return new external_function_parameters([
            'success' => new external_value(PARAM_BOOL, 'Whether everything was successful or not.'),
            'message' => new external_value(PARAM_RAW, 'Message (usually the error message).'),
            'form' => new external_value(PARAM_RAW, 'form html to allow editing billing data.'),
        ]);
    }
}