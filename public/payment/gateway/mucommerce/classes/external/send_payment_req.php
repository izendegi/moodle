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

class send_payment_req extends external_api {
    private const RESP_PARAM_URL = 'payUrl';
    private const RESP_PARAM_ORDER = 'idPedido';
    
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'component' => new external_value(PARAM_COMPONENT, 'The component name'),
            'paymentarea' => new external_value(PARAM_AREA, 'Payment area in the component'),
            'itemid' => new external_value(PARAM_INT, 'The item id in the context of the component area'),
            'courseid' => new external_value(PARAM_INT, 'The course id'),
        ]);
    }
    
    /**
     * Perform what needs to be done when a transaction is reported to be complete.
     * This function does not take cost as a parameter as we cannot rely on any provided value.
     *
     * @param string $component Name of the component that the itemid belongs to
     * @param string $paymentarea
     * @param int $itemid An internal identifier that is used by the component
     * @param int $courseid
     * @return array
     */
    public static function execute(string $component, string $paymentarea, int $itemid, int $courseid): array {
        global $USER, $DB;
        
        require_login();
        
        self::validate_parameters(self::execute_parameters(), [
            'component' => $component,
            'paymentarea' => $paymentarea,
            'itemid' => $itemid,
            'courseid' => $courseid
        ]);
        
        $usr = $DB->get_record('user', array('id' => $USER->id, 'deleted' => 0), '*', MUST_EXIST);
        $crs = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $context = \context_course::instance($crs->id);
        $success = false;
        $message = '';
        $url = '';
        $form = '';
        if(!mucommerce_enrolment_checker::is_enrolled($context, $usr->id, '', true)) {
            $rsltPaymentIsMade = external_api::call_external_function('paygw_mucommerce_check_payment_is_made', array('courseid' => $crs->id, 'userid' => $usr->id));
            
            if(!$rsltPaymentIsMade['error']){
                if(!$rsltPaymentIsMade['data']['success']) {
                    $config = (object)helper::get_gateway_configuration($component, $paymentarea, $itemid, 'mucommerce');
                    $sandbox = $config->environment == 'sandbox';
                    
                    $payable = payment_helper::get_payable($component, $paymentarea, $itemid);
                    $currency = $payable->get_currency();
                    
                    // Add surcharge if there is any.
                    $surcharge = helper::get_gateway_surcharge('mucommerce');
                    $amount = helper::get_rounded_cost($payable->get_amount(), $currency, $surcharge);
                    
                    $mucommercehelper = new mucommerce_helper($config->urlbase, $config->user, $config->pwd, $sandbox);
                    
                    $fee = 0;
                    
                    $paymentResp = $mucommercehelper->make_payment_req($payable->get_amount(), $fee, $crs); //, $currency
                    
                    if($paymentResp) {
                        $form = self::checkIfHasPersonalInfoError($paymentResp, $DB, $usr);
                        
                        if(empty($form)) {
                            $url = $paymentResp[self::RESP_PARAM_URL];
                            
                            if(!is_null($url) && !empty($url)) {
                                $success = true;
                                
                                $orderId = $paymentResp[self::RESP_PARAM_ORDER];
                                if(!is_null($orderId)){
                                    try {
                                        // Store Mucommerce extra information.
                                        $record = new \stdClass();
                                        $record->courseid = $crs->id;
                                        $record->userid = $usr->id;
                                        $record->component = $component;
                                        $record->paymentarea = $paymentarea;
                                        $record->itemid = $itemid;
                                        $record->environment = $config->environment;
                                        $record->mucom_orderid = $orderId;
                                        $record->is_paid = 'E';
                                        
                                        $DB->insert_record('paygw_mucommerce', $record);
                                    } catch (\Exception $e) {
                                        debugging('Exception while trying to insert mucommerce payment extra info: ' . $e->getMessage(), DEBUG_DEVELOPER);
                                        $success = false;
                                        $message = get_string('errorinsertextinf', 'paygw_mucommerce');
                                    }
                                } else {
                                    $message = get_string('errororderid', 'paygw_mucommerce');
                                }
                            } else {
                                $message = get_string('errorpayurl', 'paygw_mucommerce');
                            }
                        } else {
                            $message = get_string('errorpersonaldata', 'paygw_mucommerce');
                        }
                    } else {
                        $message = get_string('errornopaymentresponse', 'paygw_mucommerce');
                    }
                } else {
                    $success = true;
                }
            } else {
                $message = $rsltPaymentIsMade['exception']->message;
            }
        } else {
            $success = true;
        }
        return [
            'success' => $success,
            'message' => $message,
            'url' => $url,
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
            'url' => new external_value(PARAM_TEXT, 'Url to be redirected to make the payment'),
            'form' => new external_value(PARAM_RAW, 'Form with required fields to be updated.'),
        ]);
    }
    
    private static function checkIfHasPersonalInfoError($paymentResp, $db, $user) {
        $errors = null;
        if(array_key_exists('errors', $paymentResp)) $errors = $paymentResp['errors'];
        
        $personalInfoErrors = null;
        $form = '';
        
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:editownprofile', $context);
        $userdataform = new userdataform();
        $hasErrors = false;
        if(!is_null($errors) && sizeof($errors) > 0 && !is_null($userdataform)) {
            $data = $userdataform->get_data();
            $data['changeinvoiceaddress'] = 1;
            $userdataform->set_data($data);
            foreach($errors as $error){
                if(!is_null($error) && is_array($error) && sizeof($error) > 0) {
                    foreach ($error as $keyElement => $elementError) {
                        if(!is_null($elementError) && is_array($elementError) && sizeof($elementError) > 0) {                            
                                foreach ($elementError as $fields) {
                                    if(!is_null($fields) && is_array($fields) && sizeof($fields) > 0) {
                                        foreach ($fields as $keyField => $field) {
                                            $fieldName = null;
                                            if(!is_null($field) && is_array($field) && sizeof($field) > 0) {
                                                if(strcasecmp($keyElement, "cliente") == 0 && !strcasecmp($keyField, "contacto") == 0) {
                                                    switch (strtolower($keyField)){
                                                        case 'cifdni':
                                                            $fieldName = UserFields::USER_FIELD_INVOICE_CIF;
                                                            break;
                                                        case 'nombre':
                                                            $fieldName = UserFields::USER_FIELD_INVOICE_NOMBRE;
                                                            break;
                                                        case 'direccion':
                                                            $fieldName = UserFields::USER_FIELD_INVOICE_DIRECCION;
                                                            break;
                                                        case 'cp':
                                                            $fieldName = UserFields::USER_FIELD_INVOICE_CP;
                                                            break;
                                                        case 'municipio':
                                                            $fieldName = UserFields::USER_FIELD_INVOICE_MUNICIPIO;
                                                            break;
                                                        case 'email':
                                                            $fieldName = UserFields::USER_FIELD_INVOICE_EMAIL;
                                                            break;
                                                        case 'telefono':
                                                            $fieldName = UserFields::USER_FIELD_INVOICE_TELEFONO;
                                                            break;
                                                    }
                                                }
                                                
                                                foreach ($field as $elmErr){
                                                    if(!is_null($fieldName) && !empty($fieldName)) {
                                                        $userdataform->setElementsErrors($fieldName, $elmErr);
                                                        $hasErrors = true;
                                                    }
                                                    error_log('+++send payment request error: '.$elmErr);
                                                }
                                            }
                                        }
                                    }
                                }
                        }
                    }
                }
            }
            
            if($hasErrors) $form = $userdataform->render();
        }
        
        return $form;
    }
}