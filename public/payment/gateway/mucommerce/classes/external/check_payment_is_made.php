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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/config.php');

require_once($CFG->libdir . '/externallib.php');

class check_payment_is_made extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'The course id'),
            'userid' => new external_value(PARAM_INT, 'The user id of the student'),
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
    public static function execute(int $courseid, int $userid): array {
        global $DB;
        
        self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'userid' => $userid,
        ]);
        
        $message = '';
        $success = true;
        
        try {
            $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
            $context = \context_course::instance($course->id);

            if(!mucommerce_enrolment_checker::is_enrolled($context, $user->id, '', true)) {
                $extraInfos = $DB->get_records('paygw_mucommerce', array('courseid' => $course->id,'userid' => $user->id), 'id DESC');
                
                $environment = null;
                $arrOrderIds = array();
                $isPaid = false;
                foreach ($extraInfos as $extraInfo) {
                    if($extraInfo) {
                        if(is_null($environment)) $environment = $extraInfo->environment;
                        
                        $config = (object)helper::get_gateway_configuration($extraInfo->component, $extraInfo->paymentarea, (int)$extraInfo->itemid, 'mucommerce');
                        $sandbox = $config->environment == 'sandbox';
                        
                        if($sandbox && $environment !== 'sandbox'){
                            $environment = 'sandbox';
                        } elseif(!$sandbox && $environment === 'sandbox') {
                            $environment = 'production';
                        }
                        
                        if($extraInfo->environment === $environment) {
                            if($extraInfo->is_paid == 'B') {
                                $isPaid = true;
                                break;
                            } else {
                                $arrOrderIds[] = $extraInfo->mucom_orderid;
                            }
                        }
                    }
                }
                
                $userId = (int)$user->id;
                if(!$isPaid) {
                    if(isset($config)){
                        $mucommercehelper = new mucommerce_helper($config->urlbase, $config->user, $config->pwd, $sandbox);
                        if(!is_null($arrOrderIds) && sizeof($arrOrderIds) > 0) {
                            $courseId = (int)$course->id;
                            $idPed = $mucommercehelper->order_paid($courseId, $userId, $arrOrderIds);
                            
                            if($idPed > 0) {
                                $order = $DB->get_record('paygw_mucommerce', array('courseid' => $courseId,'userid' => $userId, 'mucom_orderid' => $idPed), '*', MUST_EXIST);
                                if(!is_null($order)) {
                                    try{
                                        $order->is_paid = 'B';
                                        $DB->update_record('paygw_mucommerce', $order);
                                        payment_helper::deliver_order($order->component, $order->paymentarea, $order->itemid, -1, $userId);
                                    } catch (\Exception $e1) {
                                        debugging('Exception while trying to update and deliver order on check_payment_is_made: ' . $e1->getMessage(), DEBUG_DEVELOPER);
                                        $success = false;
                                        $message = get_string('errornotupdatestatus', 'paygw_mucommerce');
                                    }
                                } else {
                                    $success = false;
                                    $message = get_string('errornotorderfound', 'paygw_mucommerce');
                                }
                            } else {
                                $success = false;
                                $message = get_string('errornotenrol', 'paygw_mucommerce');
                            }
                        } else {
                            $success = false;
                            $message = get_string('errornotorderids', 'paygw_mucommerce');
                        }
                    } else {
                        $success = false;
                    }
                } else {
                    try{
                        payment_helper::deliver_order($extraInfo->component, $extraInfo->paymentarea, (int)$extraInfo->itemid, -1, $userId);
                    } catch (\Exception $e2) {
                        debugging('Exception while trying to deliver order on check_payment_is_made when is paid: ' . $e2->getMessage(), DEBUG_DEVELOPER);
                        $success = false;
                        $message = get_string('errornotupdatestatus', 'paygw_mucommerce');
                    }
                }
            }
        } catch (\Exception $e) {
            debugging('Exception in check_payment_is_made: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $success = false;
            $message = get_string('internalerrorcheckpayment', 'paygw_mucommerce');
        }
        
        return [
            'success' => $success,
            'message' => $message,
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
        ]);
    }
}