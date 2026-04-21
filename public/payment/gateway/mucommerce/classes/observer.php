<?php

/**
 * Event observer for Mucomerce payment gateway plugin.
 *
 * @package    paygw_mucommerce
 * @copyright  2023 Onenpro <info@onenpro.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;

class paygw_mucommerce_observer {
    
    public static function check_user_course_enrolment(\core\event\user_loggedin $event) {
        return self::checkPayment($event);
    }
    
    public static function check_user_loggedinas_course_enrolment(\core\event\user_loggedinas $event) {
        return self::checkPayment($event);
    }
    
    public static function check_user_course_enrolment_when_viewed(\core\event\course_viewed $event) {
        return self::checkPayment($event);
    }
    
    /*PRIVATE METHODS*/
    
    private static function checkPayment($event) {
        global $DB;
        
        try{
            $userid = !empty($event->relateduserid) ? $event->relateduserid : $event->userid;
            
            $courseIds = $DB->get_records_sql('SELECT distinct p1.courseid FROM {paygw_mucommerce} p1 WHERE p1.userid = :userid AND p1.is_paid = :notpaid and p1.courseid not in (SELECT distinct p2.courseid FROM {paygw_mucommerce} p2 WHERE p2.userid = :userid2 AND p2.is_paid = :ispaid) ORDER BY p1.courseid DESC', ['userid' => $userid, 'notpaid' => 'E', 'userid2' => $userid, 'ispaid' => 'B']);
            
            $hasError = false;
            if(!is_null($courseIds) && sizeof($courseIds) > 0) {
                foreach ($courseIds as $courseId) {
                    if($courseId) {
                        $rsltPaymentIsMade = external_api::call_external_function('paygw_mucommerce_check_payment_is_made', array('courseid' => $courseId, 'userid' => $userid));
                        
                        if(!$rsltPaymentIsMade['error']){
                            $hasError = true;
                        }
                    }
                }
            }
            
            if($hasError) return false;
        } catch (\Exception $e) {
            debugging('Exception while trying to check payment is made in obsever: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
        return true;
    }
}