<?php

/**
 * Mucomerce payment gateway plugin event handler definition.
 *
 * @package    paygw_mucommerce
 * @copyright  2023 Onenpro <info@onenpro.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
    
    array(
        'eventname'   => '\core\event\user_loggedin',
        'callback'    => 'paygw_mucommerce_observer::check_user_course_enrolment',
    ),
    array(
        'eventname'   => '\core\event\user_loggedinas',
        'callback'    => 'paygw_mucommerce_observer::check_user_loggedinas_course_enrolment',
    ),
    array(
        'eventname'   => '\core\event\course_viewed',
        'callback'    => 'paygw_mucommerce_observer::check_user_course_enrolment_when_viewed',
    ),
);