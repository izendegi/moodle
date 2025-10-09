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

class save_user_data extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'formdata' => new external_value(PARAM_RAW, 'The user new data to save get it through from')
        ]);
    }
    
    /**
     * Perform what needs to be done when a transaction is reported to be complete.
     * This function does not take cost as a parameter as we cannot rely on any provided value.
     *
     * @param string $component Name of the component that the itemid belongs to
     * @param string $paymentarea
     * @param int $itemid An internal identifier that is used by the component
     * @return array
     */
    public static function execute(string $formdatastr): array {
        global $DB, $USER;
        
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/user:editownprofile', $context);
        
        $form = null;
        $formdata = array();
        
        $params = self::validate_parameters(self::execute_parameters(), [
            'formdata' => $formdatastr,
        ]);
        
        $serialiseddata = json_decode($params['formdata']);
        
        parse_str($serialiseddata, $formdata);
        
        $userdataform = new userdataform(null, null, 'post', '', null, true, $formdata);
        
        $validateddata = $userdataform->get_data();
        
        $message = get_string('saveuserdatanotupdate', 'paygw_mucommerce');
        $success = false;
        
        try {
            if(!is_null($validateddata)) {
                    $dbUser = $DB->get_record('user', array('id' => $USER->id, 'deleted' => 0), '*', MUST_EXIST);
                    
                    if(!is_null($dbUser)) {
                        foreach (UserFields::getAllUserFields() as $userField) {
                            $fieldname = null;
                            if(property_exists($validateddata, $userField)) {
                                switch ($userField){
                                    case UserFields::USER_FIELD_DNI:
                                        $fieldname = UserFields::USER_DB_FIELD_DNI;
                                    case UserFields::USER_FIELD_INVOICE_CIF:
                                        if(is_null($fieldname)) $fieldname = UserFields::USER_DB_FIELD_INVOICE_CIF;
                                    case UserFields::USER_FIELD_INVOICE_NOMBRE:
                                        if(is_null($fieldname)) $fieldname = UserFields::USER_DB_FIELD_INVOICE_NOMBRE;
                                    case UserFields::USER_FIELD_INVOICE_DIRECCION:
                                        if(is_null($fieldname)) $fieldname = UserFields::USER_DB_FIELD_INVOICE_DIRECCION;
                                    case UserFields::USER_FIELD_INVOICE_MUNICIPIO:
                                        if(is_null($fieldname)) $fieldname = UserFields::USER_DB_FIELD_INVOICE_MUNICIPIO;
                                    case UserFields::USER_FIELD_INVOICE_CP:
                                        if(is_null($fieldname)) $fieldname = UserFields::USER_DB_FIELD_INVOICE_CP;
                                    case UserFields::USER_FIELD_INVOICE_EMAIL:
                                        if(is_null($fieldname)) $fieldname = UserFields::USER_DB_FIELD_INVOICE_EMAIL;
                                    case UserFields::USER_FIELD_INVOICE_TELEFONO:
                                        if(is_null($fieldname)) $fieldname = UserFields::USER_DB_FIELD_INVOICE_TELEFONO;
                                    case UserFields::USER_FIELD_CP:
                                        if(is_null($fieldname)) $fieldname = UserFields::USER_DB_FIELD_CP;
                                        try {
                                            $fieldId = $DB->get_field_sql('select id from {user_info_field} where UPPER(shortname) = UPPER(:fieldname)', array('fieldname' => $fieldname));
                                            if($fieldId) {
                                                $dataObj = new \stdClass();
                                                $dataObj->userid  = $dbUser->id;
                                                $dataObj->fieldid = $fieldId;
                                                $dataObj->data    = $validateddata->{$userField};
                                                
                                                if($dataid = $DB->get_field('user_info_data', 'id', array('userid' => $dataObj->userid, 'fieldid' => $dataObj->fieldid))) {
                                                    $dataObj->id = $dataid;
                                                    $editResult = $DB->update_record('user_info_data', $dataObj);
                                                } else {
                                                    $editResult = $DB->insert_record('user_info_data', $dataObj, false);
                                                }
                                                
                                                if($editResult) {
                                                    $success = true;
                                                    $message = '';
                                                }
                                            }
                                        } catch(\Exception $e1) {
                                            debugging('Exception in save_user_data: ' . $e1->getMessage(), DEBUG_DEVELOPER);
                                        }
                                        break;
                                    default:
                                        $dbUser->$userField = $validateddata->{$userField};
                                        try {
                                            $DB->update_record('user', $dbUser);
                                            $USER->$userField= $validateddata->{$userField};
                                            $_SESSION['USER']= $USER;
                                            $success = true;
                                            $message = '';
                                        } catch (\Exception $e2) {
                                            debugging('Exception in save_user_data: ' . $e2->getMessage(), DEBUG_DEVELOPER);
                                        }
                                        break;
                                }
                            }
                        }
                    } else {
                        $message = get_string('saveuserdatausernotexists', 'paygw_mucommerce');
                    }
            } else {
                $message = get_string('saveuserdatanodatareceived', 'paygw_mucommerce');
                $form = $userdataform->render();
            }
        } catch (\Exception $e) {
            debugging('Exception in save_user_data: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $message = get_string('internalerrorsaveuserdata', 'paygw_mucommerce');
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
            'form' => new external_value(PARAM_RAW, 'form to be rerendered with errors.'),
        ]);
    }
}