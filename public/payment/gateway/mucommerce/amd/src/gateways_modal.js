/* eslint-disable no-tabs */
/**
 * This module is responsible for Mucommerce content in the gateways modal.
 *
 * @module     paygw_mucommerce/gateway_modal
 * @copyright  2023 Onenpro <info@onenpro.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Repository from './repository';
import Templates from 'core/templates';
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import ModalSaveCancel from 'core/modal_save_cancel';
import Notification from 'core/notification';
import * as Str from 'core/str';

/**
 * Creates and shows a modal that contains a placeholder.
 *
 * @returns {Promise<Modal>}
 */
const showModalWithPlaceholder = async () => await Modal.create({
	body: await Templates.render('paygw_mucommerce/mucommerce_button_placeholder', {}),
	show: true,
	removeOnClose: true,
});

/**
 * Creates and shows a modal.
 *
 * @param {string} bodyform form to ask for required user data to make the payment
 * @returns {Promise<Modal>}
 */
const showEditRequiredPersonalDataModal = async (bodyform) => await ModalSaveCancel.create({
	large: true,
	title: Str.get_string('userdataformtitle', 'paygw_mucommerce'),
	body: bodyform,
	buttons: {
		save: Str.get_string('userdataformsavebtn', 'paygw_mucommerce'),
	},
	removeOnClose: true,
	show: true,
});

/**
 * Process the payment.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {number} itemId An internal identifier that is used by the component
 * @returns {Promise<string>}
 */
export const process = (component, paymentArea, itemId) => {
	return Promise.resolve(showModalWithPlaceholder())
		.then(modal => {
			modal.getRoot().on(ModalEvents.hidden, () => {
				// Destroy when hidden.
				modal.destroy();
			});

			let body = document.getElementsByTagName('body')[0];
			let crsId = null;
			let bdClassList = null;
			if (body !== null && typeof body !== 'undefined') {
				bdClassList = body.classList;
				bdClassList.forEach(
					function(classItem) {
						if (classItem.startsWith('course-')) {
							crsId = classItem.split('course-')[1];
						}
					}
				);
				if (crsId !== null && typeof crsId !== 'undefined') {
					let courseId = parseInt(crsId);
					return Promise.all([modal, askForDataToUser(modal), courseId]);
				}
			}

			modal.hide();
			return Promise.reject('courseid is missing');
		})
		.then(([modal, resSavedData, courseId]) => {
			return checkSaveBllingDataResult(component, paymentArea, itemId, modal, resSavedData, courseId);
		});
};

const checkSaveBllingDataResult = (component, paymentArea, itemId, modal, resSavedData, courseId) => {
	if (resSavedData.success) {
		return Promise.all([modal, getMucommercePaymentUrl(component, paymentArea, itemId, courseId)])
			.then(([modal, resUrl]) => {
				if (resUrl.success) {
					if (resUrl.url !== 'undefined' && resUrl.url !== '') {
						location.href = resUrl.url;
						return new Promise(() => null);
					}
					modal.hide();
					return Promise.resolve(resUrl.message);
				} else {
					if (resUrl.form !== 'undefined' && resUrl.form !== '') {
						return Promise.all([modal, openEditBillingInfoModal(modal, resUrl.form), courseId])
							.then(([modal, resSavedData, courseId]) => {
								return checkSaveBllingDataResult(component, paymentArea, itemId, modal, resSavedData, courseId);
							});
					} else {
						modal.hide();
						return Promise.reject(resUrl.message);
					}
				}
			});
	} else {
		return Promise.reject(resSavedData.message);
	}
};

const getMucommercePaymentUrl = (component, paymentArea, itemId, courseId) => {
	return new Promise(resolve => {
		Repository.getPaymentUrl(component, paymentArea, itemId, courseId)
			.then(res => {
				return res;
			})
			.then(resolve);
	});
};

const saveUserData = (modalEditRequiredPersonalData) => {
	return new Promise(savedUserDataResult => {
		modalEditRequiredPersonalData.getRoot().find('form').find('.msg-generic')[0].style.display = 'none';
		Repository.saveRequiredUserData(
			modalEditRequiredPersonalData.getRoot().find('form').serialize())
			.then(savedres => {
				if (savedres.success) {
					modalEditRequiredPersonalData.hide();
					return savedres;
				} else {
					if (savedres.form !== null) {
						modalEditRequiredPersonalData.setBody(savedres.form);
						addModalEditBillingDataChangeABillingddressListener(modalEditRequiredPersonalData);
						checkIfNeedToDisplayBilligInfo(modalEditRequiredPersonalData);
						savedres.reject();
					} else {
						modalEditRequiredPersonalData.getRoot().find('form').find('.msg-generic').find('.msg-error')
							.text(savedres.message);
						modalEditRequiredPersonalData.getRoot().find('form').find('.msg-generic')[0].style.display = 'block';
						addModalEditBillingDataChangeABillingddressListener(modalEditRequiredPersonalData);
						checkIfNeedToDisplayBilligInfo(modalEditRequiredPersonalData);
						savedres.reject();
					}
				}
			}).then(savedUserDataResult);
	});
};

const askForDataToUser = (parentmodal) => {
	return new Promise(editBillingDataForm => {
		Repository.getEditBillingDataForm()
			.then(resultBillForm => {
				if (resultBillForm.success) {
					if (resultBillForm.form) {
						return openEditBillingInfoModal(parentmodal, resultBillForm.form);
					} else {
						return resultBillForm;
					}
				} else {
					return resultBillForm;
				}
			}).then(editBillingDataForm);
	});
};

const openEditBillingInfoModal = (parentmodal, form) => {
	return new Promise(saveBillingDataResult => {
		showEditRequiredPersonalDataModal(form)
			.then(modalEditBillingData => {
				return new Promise(result => {
					modalEditBillingData.getRoot().on(ModalEvents.save, e => {
						e.preventDefault();
						if (!modalEditBillingData.getRoot().find('input[name=changeinvoiceaddress][type=checkbox]')[0].checked) {
							fillBillingWithStudentData(modalEditBillingData);
						}
						saveUserData(modalEditBillingData)
							.then(result)
							.catch(Notification.exception);
					});

					modalEditBillingData.getRoot().on(ModalEvents.hidden, () => {
						modalEditBillingData.destroy();
						cif = name = address = city = cp = email = phone = null;
						parentmodal.hide();
					});

					addModalEditBillingDataChangeABillingddressListener(modalEditBillingData);
					checkIfNeedToDisplayBilligInfo(modalEditBillingData);
				});
			}).then(saveBillingDataResult)
			.catch(Notification.exception);
	});
};

const fillBillingWithStudentData = (modalEditBillingData) => {
	const billingCif = modalEditBillingData.getRoot().find('#id_profile_field_DNI')[0].value;
	modalEditBillingData.getRoot().find('#id_profile_field_BILLING_CIF')[0].value = billingCif;
	let stdLastname = modalEditBillingData.getRoot().find('#id_lastname')[0].value;
	modalEditBillingData.getRoot().find('#id_profile_field_BILLING_NAME')[0].value =
		modalEditBillingData.getRoot().find('#id_firstname')[0].value +
		(stdLastname ? stdLastname : '');
	modalEditBillingData.getRoot().find('#id_profile_field_BILLING_ADDRESS')[0].value =
		modalEditBillingData.getRoot().find('#id_address')[0].value;
	modalEditBillingData.getRoot().find('#id_profile_field_BILLING_CITY')[0].value =
		modalEditBillingData.getRoot().find('#id_city')[0].value;
	modalEditBillingData.getRoot().find('#id_profile_field_BILLING_CP')[0].value =
		modalEditBillingData.getRoot().find('#id_profile_field_CP')[0].value;
	modalEditBillingData.getRoot().find('#id_profile_field_BILLING_EMAIL')[0].value =
		modalEditBillingData.getRoot().find('#id_email')[0].value;
	modalEditBillingData.getRoot().find('#id_profile_field_BILLING_PHONE')[0].value =
		modalEditBillingData.getRoot().find('#id_phone2')[0].value;
};

const clearBillingData = (modalEditBillingData) => {
	let billingInputs = modalEditBillingData.getRoot().find('#id_billinghdrcontainer input');

	if (billingInputs !== null && typeof billingInputs !== 'undefined' && billingInputs.length > 0) {
		for (let i = 0; i < billingInputs.length; i++) {
			billingInputs[i].value = '';
		}
	}
};

const addModalEditBillingDataChangeABillingddressListener = (modalEditBillingData) => {
	modalEditBillingData.getRoot().find('input[name=changeinvoiceaddress][type=checkbox]')[0].addEventListener('change', event => {
		if (event.target.checked) {
			if (cif === null || typeof cif === 'undefined') {
				cif = modalEditBillingData.getRoot().find('#id_profile_field_BILLING_CIF')[0].value;
			}
			if (name === null || typeof name === 'undefined') {
				name = modalEditBillingData.getRoot().find('#id_profile_field_BILLING_NAME')[0].value;
			}
			if (address === null || typeof address === 'undefined') {
				address = modalEditBillingData.getRoot().find('#id_profile_field_BILLING_ADDRESS')[0].value;
			}
			if (city === null || typeof city === 'undefined') {
				city = modalEditBillingData.getRoot().find('#id_profile_field_BILLING_CITY')[0].value;
			}
			if (cp === null || typeof cp === 'undefined') {
				cp = modalEditBillingData.getRoot().find('#id_profile_field_BILLING_CP')[0].value;
			}
			if (email === null || typeof email === 'undefined') {
				email = modalEditBillingData.getRoot().find('#id_profile_field_BILLING_EMAIL')[0].value;
			}
			if (phone === null || typeof phone === 'undefined') {
				phone = modalEditBillingData.getRoot().find('#id_profile_field_BILLING_PHONE')[0].value;
			}

			if (cif !== null && typeof cif !== 'undefined' && cif !== '' ||
				name !== null && typeof name !== 'undefined' && name !== '' ||
				address !== null && typeof address !== 'undefined' && address !== '' ||
				city !== null && typeof city !== 'undefined' && city !== '' ||
				cp !== null && typeof cp !== 'undefined' && cp !== '' ||
				email !== null && typeof email !== 'undefined' && email !== '' ||
				phone !== null && typeof phone !== 'undefined' && phone !== '') {
				modalEditBillingData.getRoot().find('#id_profile_field_BILLING_CIF')[0].value = cif;
				modalEditBillingData.getRoot().find('#id_profile_field_BILLING_NAME')[0].value = name;
				modalEditBillingData.getRoot().find('#id_profile_field_BILLING_ADDRESS')[0].value = address;
				modalEditBillingData.getRoot().find('#id_profile_field_BILLING_CITY')[0].value = city;
				modalEditBillingData.getRoot().find('#id_profile_field_BILLING_CP')[0].value = cp;
				modalEditBillingData.getRoot().find('#id_profile_field_BILLING_EMAIL')[0].value = email;
				modalEditBillingData.getRoot().find('#id_profile_field_BILLING_PHONE')[0].value = phone;
				modalEditBillingData.getRoot().find('#id_billinghdr')[0].classList.add('display-elm');
			} else {
				fillBillingWithStudentData(modalEditBillingData);
			}
		} else {
			modalEditBillingData.getRoot().find('#id_billinghdr')[0].classList.remove('display-elm');
			clearBillingData(modalEditBillingData);
		}
	});
};

const checkIfNeedToDisplayBilligInfo = (modalEditBillingData) => {
	if (modalEditBillingData.getRoot().find('input[name=changeinvoiceaddress][type=checkbox]')[0].checked) {
		modalEditBillingData.getRoot().find('#id_billinghdr')[0].classList.add('display-elm');
	}
};

let cif, name, address, city, cp, email, phone;