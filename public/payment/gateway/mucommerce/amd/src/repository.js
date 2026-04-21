/**
 * Mucommerce repository module to encapsulate all of the AJAX requests that can be sent for paygw_mucommerce.
 *
 * @module     paygw_mucommerce
 * @copyright  2023 Onenpro <info@onenpro.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

/**
 * Call server to validate and capture payment for order.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {number} itemId An internal identifier that is used by the component
 * @param {number} courseId
 * @returns {*}
 */
export const getPaymentUrl = (component, paymentArea, itemId, courseId) => {
    const request = {
        methodname: 'paygw_mucommerce_send_payment_req',
        args: {
            component,
            paymentarea: paymentArea,
            itemid: itemId,
            courseid: courseId,
        },
    };

    return Ajax.call([request])[0];
};

/**
 * Call server to save user required data to make the payment.
 * @param {string} formData The data uploaded though the form
 * @returns {*}
 *
 */
export const saveRequiredUserData = (formData) => {
    const request = {
        methodname: 'paygw_mucommerce_save_user_data',
        args: {
            formdata: JSON.stringify(formData),
        }
    };

    return Ajax.call([request])[0];
};

/**
 * Call server to get billing data form to edit user\'s data the payment.
 * no params
 * @returns {*}
 *
 */
export const getEditBillingDataForm = () => {
    const request = {
        methodname: 'paygw_mucommerce_get_billing_data_form',
        args: {

        },
    };

    return Ajax.call([request])[0];
};
