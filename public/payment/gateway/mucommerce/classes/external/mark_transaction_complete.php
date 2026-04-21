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
use paygw_mucommerce\mucommerce_enrolment_checker;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/config.php');

require_once($CFG->libdir . '/externallib.php');

class mark_transaction_complete extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'idExt' => new external_value(PARAM_INT, 'The course id'),
            'idUser' => new external_value(PARAM_INT, 'The user id of the student'),
            'idOrder' => new external_value(PARAM_INT, 'The order id of the mucommerce'),
        ]);
    }
    
    /**
     * Perform what needs to be done when a transaction is reported to be complete.
     *
     *
     * @param string $courseid
     * @param string $userid
     * @return array
     */
    public static function execute(int $courseid, int $userid, int $orderid): array {
        global $DB;
        
        $success = false;
        $message = '';
        
        self::validate_parameters(self::execute_parameters(), [
            'idExt' => $courseid,
            'idUser' => $userid,
            'idOrder' => $orderid,
        ]);
        
        try {
            $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
            $context = \context_course::instance($course->id);
            
            $order = $DB->get_record('paygw_mucommerce', array('courseid' => $course->id,'userid' => $user->id, 'mucom_orderid' => $orderid), '*', MUST_EXIST);
            if(!is_null($order)) {
                try{
                    if(strcasecmp($order->is_paid, 'B') != 0) {
                        $order->is_paid = 'B';
                        $DB->update_record('paygw_mucommerce', $order);
                    }
                    if(!mucommerce_enrolment_checker::is_enrolled($context, $user->id, '', true)) payment_helper::deliver_order($order->component, $order->paymentarea, (int)$order->itemid, -1, (int)$user->id);
                    $success = true;
                } catch (\Exception $e1) {
                    debugging('Exception while trying to update and deliver order on mark_transaction_complete ' . $e1->getMessage(), DEBUG_DEVELOPER);
                    $success = false;
                    $message = get_string('errornotupdatestatus', 'paygw_mucommerce');
                }
            } else {
                $success = false;
                $message = get_string('errornotorderfound', 'paygw_mucommerce');
            }
        } catch (\Exception $e) {
            debugging('Exception while trying to get user or course from database ' . $e->getMessage(), DEBUG_DEVELOPER);
            $success = false;
            $message = get_string('errornotusercourse', 'paygw_mucommerce');
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