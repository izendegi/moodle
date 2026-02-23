<?php
namespace paygw_mucommerce\form;

use paygw_mucommerce\UserFields;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

/**
 * Contact DPO modal form
 *
 * @package    paygw_mucommerce
 * @copyright  2024 onenpro
 * 
 */
class userdataform extends \moodleform {
    /**
     * Form definition
     */
    protected function definition() {
        global $USER, $DB;
        
        $usr = $DB->get_record('user', array('id' => $USER->id, 'deleted' => 0), '*', MUST_EXIST);
        
        $mform = $this->_form;
        $presetdata = $this->_ajaxformdata;
        
        $attrs = $mform->getAttributes();
        $attrs['class'] = $attrs['class'].' paygw-mucommerce-billing-form';
        $mform->setAttributes($attrs);
        
        $mform->addElement('html', '<div class="msg-generic" style="text-align: center; padding: 5px; display: none;"><span class="msg-error" style="color: #b80b0b;"></span></div>');
                
        $mform->addElement('header', 'personalhdr', get_string('userdatapersonalinfohdr', 'paygw_mucommerce'));
        $mform->setExpanded('personalhdr');
        
        $mform->addElement('text', UserFields::USER_FIELD_DNI, get_string('userdatafield'.str_replace('_','',strtolower(UserFields::USER_FIELD_DNI)), 'paygw_mucommerce'), 'maxlength="255"');
        $mform->setType(UserFields::USER_FIELD_DNI, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_DNI, get_string('required'), 'required', null, 'client');
//         $mform->addRule(UserFields::USER_FIELD_DNI, get_string('userdatadnincorrectformat', 'paygw_mucommerce'), 'regex', '[0-9A-Za-z\. \-,\/\(\)\+]+', 'client');
        $usrDni = self::getUserCustomFieldValue($DB, $usr->id, UserFields::USER_DB_FIELD_DNI);
        $defaultData[UserFields::USER_FIELD_DNI] = $usrDni; 
        
        $mform->addElement('text', UserFields::USER_FIELD_NOMBRE, get_string('userdatafield'.UserFields::USER_FIELD_NOMBRE, 'paygw_mucommerce'), 'maxlength="100"');
        $mform->setType(UserFields::USER_FIELD_NOMBRE, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_NOMBRE, get_string('required'), 'required', null, 'client');
        $fieldname = UserFields::USER_FIELD_NOMBRE;
        $usrNombre = $usr->$fieldname;
        $defaultData[UserFields::USER_FIELD_NOMBRE] = $usrNombre;
        
        $mform->addElement('text', UserFields::USER_FIELD_APELLIDOS, get_string('userdatafield'.UserFields::USER_FIELD_APELLIDOS, 'paygw_mucommerce'), 'maxlength="155"');
        $mform->setType(UserFields::USER_FIELD_APELLIDOS, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_APELLIDOS, get_string('required'), 'required', null, 'client');
        $fieldname = UserFields::USER_FIELD_APELLIDOS;
        $usrApl = $usr->$fieldname;
        $defaultData[$fieldname] = $usrApl;
        
        $mform->addElement('text', UserFields::USER_FIELD_DIRECCION, get_string('userdatafield'.UserFields::USER_FIELD_DIRECCION, 'paygw_mucommerce'), 'maxlength="255"');
        $mform->setType(UserFields::USER_FIELD_DIRECCION, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_DIRECCION, get_string('required'), 'required', null, 'client');
        $fieldname = UserFields::USER_FIELD_DIRECCION;
        $usrDir = $usr->$fieldname;
        $defaultData[$fieldname] = $usrDir;
        
        $mform->addElement('text', UserFields::USER_FIELD_MUNICIPIO, get_string('userdatafield'.UserFields::USER_FIELD_MUNICIPIO, 'paygw_mucommerce'), 'maxlength="45"');
        $mform->setType(UserFields::USER_FIELD_MUNICIPIO, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_MUNICIPIO, get_string('required'), 'required', null, 'client');
        $fieldname = UserFields::USER_FIELD_MUNICIPIO;
        $usrMunc = $usr->$fieldname;
        $defaultData[$fieldname] = $usrMunc;
        
        $mform->addElement('text', UserFields::USER_FIELD_CP, get_string('userdatafield'.str_replace('_','',strtolower(UserFields::USER_FIELD_CP)), 'paygw_mucommerce'), 'maxlength="20"');
        $mform->setType(UserFields::USER_FIELD_CP, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_CP, get_string('required'), 'required', null, 'client');
        $usrCp = self::getUserCustomFieldValue($DB, $usr->id, UserFields::USER_DB_FIELD_CP);
        $defaultData[UserFields::USER_FIELD_CP] = $usrCp;
        
        $mform->addElement('text', UserFields::USER_FIELD_EMAIL, get_string('userdatafield'.UserFields::USER_FIELD_EMAIL, 'paygw_mucommerce'), 'maxlength="255"');
        $mform->setType(UserFields::USER_FIELD_EMAIL, PARAM_EMAIL);
        $mform->addRule(UserFields::USER_FIELD_EMAIL, get_string('required'), 'required', null, 'client');
        $mform->addRule(UserFields::USER_FIELD_EMAIL, get_string('userdataemailincorrectformat', 'paygw_mucommerce'), 'email', null, 'client');
        $fieldname = UserFields::USER_FIELD_EMAIL;
        $usrEmail = $usr->$fieldname;
        $defaultData[$fieldname] = $usrEmail;
        
        $mform->addElement('text', UserFields::USER_FIELD_TELEFONO2, get_string('userdatafield'.UserFields::USER_FIELD_TELEFONO2, 'paygw_mucommerce'), 'maxlength="45"');
        $mform->setType(UserFields::USER_FIELD_TELEFONO2, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_TELEFONO2, get_string('required'), 'required', null, 'client');
        $fieldname = UserFields::USER_FIELD_TELEFONO2;
        $fieldnameSecondary = UserFields::USER_FIELD_TELEFONO1;
        $usrTlfn = (!is_null($usr->$fieldname) && !empty($usr->$fieldname) ? $usr->$fieldname : $usr->$fieldnameSecondary);
        $defaultData[$fieldname] = $usrTlfn;
        
        $mform->addElement('advcheckbox', 'changeinvoiceaddress', null, get_string('userdatafieldinvoicechangeaddress', 'paygw_mucommerce'), null,  array(0, 1));
        $mform->setDefault('changeinvoiceaddress', 0);
        
        $mform->closeHeaderBefore('changeinvoiceaddress');
                
        $mform->addElement('header', 'billinghdr', get_string('userdatabillinginfohdr', 'paygw_mucommerce'));
        if(!is_null($presetdata) && isset($presetdata['changeinvoiceaddress']) && $presetdata['changeinvoiceaddress'] == 1) {
            $mform->setExpanded('billinghdr', true);
        } else {
            $mform->setExpanded('billinghdr', false);
        }       
        
        $mform->addElement('text', UserFields::USER_FIELD_INVOICE_CIF, get_string('userdatafield'.str_replace('_','',strtolower(UserFields::USER_FIELD_INVOICE_CIF)), 'paygw_mucommerce'), 'maxlength="255"');
        $mform->setType(UserFields::USER_FIELD_INVOICE_CIF, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_INVOICE_CIF, get_string('required'), 'required', null, 'client');
//         $mform->addRule(UserFields::USER_FIELD_INVOICE_CIF, get_string('userdatacifincorrectformat', 'paygw_mucommerce'), 'regex', '[0-9A-Za-z. \\-,\\/\\\\()+]*', 'client');
        $invCif = self::getUserCustomFieldValue($DB, $usr->id, UserFields::USER_DB_FIELD_INVOICE_CIF);
        $defaultData[UserFields::USER_FIELD_INVOICE_CIF] = (!is_null($invCif) && !empty($invCif) ? $invCif : $usrDni); 
        
        $mform->addElement('text', UserFields::USER_FIELD_INVOICE_NOMBRE, get_string('userdatafield'.str_replace('_','',strtolower(UserFields::USER_FIELD_INVOICE_NOMBRE)), 'paygw_mucommerce'), 'maxlength="255"');
        $mform->setType(UserFields::USER_FIELD_INVOICE_NOMBRE, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_INVOICE_NOMBRE, get_string('required'), 'required', null, 'client');
        $invNombre = self::getUserCustomFieldValue($DB, $usr->id, UserFields::USER_DB_FIELD_INVOICE_NOMBRE);
        $defaultData[UserFields::USER_FIELD_INVOICE_NOMBRE] = (!is_null($invNombre) && !empty($invNombre) ? $invNombre : $usrNombre.(!is_null($usrApl) && !empty($usrApl) ? ' '.$usrApl : ''));
        
        $mform->addElement('text', UserFields::USER_FIELD_INVOICE_DIRECCION, get_string('userdatafield'.str_replace('_','',strtolower(UserFields::USER_FIELD_INVOICE_DIRECCION)), 'paygw_mucommerce'), 'maxlength="255"');
        $mform->setType(UserFields::USER_FIELD_INVOICE_DIRECCION, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_INVOICE_DIRECCION, get_string('required'), 'required', null, 'client');
        $invDir = self::getUserCustomFieldValue($DB, $usr->id, UserFields::USER_DB_FIELD_INVOICE_DIRECCION);
        $defaultData[UserFields::USER_FIELD_INVOICE_DIRECCION] = (!is_null($invDir) && !empty($invDir) ? $invDir : $usrDir);
        
        $mform->addElement('text', UserFields::USER_FIELD_INVOICE_MUNICIPIO, get_string('userdatafield'.str_replace('_','',strtolower(UserFields::USER_FIELD_INVOICE_MUNICIPIO)), 'paygw_mucommerce'), 'maxlength="45"');
        $mform->setType(UserFields::USER_FIELD_INVOICE_MUNICIPIO, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_INVOICE_MUNICIPIO, get_string('required'), 'required', null, 'client');
        $invMunc = self::getUserCustomFieldValue($DB, $usr->id, UserFields::USER_DB_FIELD_INVOICE_MUNICIPIO);
        $defaultData[UserFields::USER_FIELD_INVOICE_MUNICIPIO] = (!is_null($invMunc) && !empty($invMunc) ? $invMunc : $usrMunc);
        
        $mform->addElement('text', UserFields::USER_FIELD_INVOICE_CP, get_string('userdatafield'.str_replace('_','',strtolower(UserFields::USER_FIELD_INVOICE_CP)), 'paygw_mucommerce'), 'maxlength="20"');
        $mform->setType(UserFields::USER_FIELD_INVOICE_CP, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_INVOICE_CP, get_string('required'), 'required', null, 'client');
        $invCp = self::getUserCustomFieldValue($DB, $usr->id, UserFields::USER_DB_FIELD_INVOICE_CP);
        $defaultData[UserFields::USER_FIELD_INVOICE_CP] = (!is_null($invCp) && !empty($invCp) ? $invCp : $usrCp);
        
        $mform->addElement('text', UserFields::USER_FIELD_INVOICE_EMAIL, get_string('userdatafield'.str_replace('_','',strtolower(UserFields::USER_FIELD_INVOICE_EMAIL)), 'paygw_mucommerce'), 'maxlength="255"');
        $mform->setType(UserFields::USER_FIELD_INVOICE_EMAIL, PARAM_EMAIL);
        $mform->addRule(UserFields::USER_FIELD_INVOICE_EMAIL, get_string('required'), 'required', null, 'client');
        $mform->addRule(UserFields::USER_FIELD_INVOICE_EMAIL, get_string('userdatainvemailincorrectformat', 'paygw_mucommerce'), 'email', null, 'client');
        $invEmail = self::getUserCustomFieldValue($DB, $usr->id, UserFields::USER_DB_FIELD_INVOICE_EMAIL);
        $defaultData[UserFields::USER_FIELD_INVOICE_EMAIL] = (!is_null($invEmail) && !empty($invEmail) ? $invEmail : $usrEmail);
        
        $mform->addElement('text', UserFields::USER_FIELD_INVOICE_TELEFONO, get_string('userdatafield'.str_replace('_','',strtolower(UserFields::USER_FIELD_INVOICE_TELEFONO)), 'paygw_mucommerce'), 'maxlength="45"');
        $mform->setType(UserFields::USER_FIELD_INVOICE_TELEFONO, PARAM_NOTAGS);
        $mform->addRule(UserFields::USER_FIELD_INVOICE_TELEFONO, get_string('required'), 'required', null, 'client');
        $invTelfn = self::getUserCustomFieldValue($DB, $usr->id, UserFields::USER_DB_FIELD_INVOICE_TELEFONO);
        $defaultData[UserFields::USER_FIELD_INVOICE_TELEFONO] = (!is_null($invTelfn) && !empty($invTelfn) ? $invTelfn : $usrTlfn);
        
        $this->set_data($defaultData);
    }
    
    public function setElementsErrors($fieldName, $errorMsg){
        $this->_form->setElementError($fieldName, $errorMsg);
        $this->_validated = false;
    }
    
    private static function getUserCustomFieldValue($db, $userid, $fieldname){
        if(!is_null($db) && !is_null($fieldname) && !empty($fieldname)) {
            $fieldData = $db->get_record_sql('select ud.* from {user_info_data} ud inner join {user_info_field} f on f.id = ud.fieldid where ud.userid = :userid and f.shortname = :fieldname',
                ['userid' => $userid, 'fieldname' => $fieldname]);
            
            if(!is_null($fieldData) && $fieldData !== false && !is_null($fieldData->data) && !empty($fieldData->data)) return $fieldData->data;
        }
        return null;
    }
    
    private static function validateBillingParamsNotEmpty($params, $errors) { 
        foreach(UserFields::USER_INVOICE_FIELDS as $userInvoiceField) {
            if(!isset($params[$userInvoiceField]) || is_null($params[$userInvoiceField]) || empty($params[$userInvoiceField]))
                $errors[$userInvoiceField] = get_string(str_replace('_','',strtolower($userInvoiceField)).'isrequired', 'paygw_mucommerce');
        }
        return $errors;
    }
}