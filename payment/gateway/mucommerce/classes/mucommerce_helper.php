<?php

/**
 * Contains helper class to work with Mucommerce REST API.
 *
 * @package    paygw_mucommerce
 * @copyright  2023 Onenpro <info@onenpro.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_mucommerce;

use core_payment\helper;
use curl;
use paygw_mucommerce\UserFields;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

class mucommerce_helper {
    
    /**
     * @var string The payment is made succesfuly
     */
    public const STATUS_COMPLETED = 'PAGADO';
    
    private const WS_PEDIDO_ELM_CLIENTE = 'cliente';
    private const WS_PEDIDO_ELM_CLIENTE_IDENTF = 'cifDni';
    private const WS_PEDIDO_ELM_CLIENTE_NOM = 'nombre';
    private const WS_PEDIDO_ELM_CLIENTE_DIR = 'direccion';
    private const WS_PEDIDO_ELM_CLIENTE_CP = 'cp';
    private const WS_PEDIDO_ELM_CLIENTE_MNCP = 'municipio';
    private const WS_PEDIDO_ELM_CLIENTE_CONTACT = 'contacto';
    private const WS_PEDIDO_ELM_CLIENTE_MAIL = 'email';
    private const WS_PEDIDO_ELM_CLIENTE_TLFN = 'telefono';
    private const WS_PEDIDO_ELM_IDM = 'idioma';
    private const WS_PEDIDO_ELM_INSTIT = 'institucion';
    private const WS_PEDIDO_ELM_CAD = 'caduca';
    private const WS_PEDIDO_ELM_NOTAS = 'notas';
    private const WS_PEDIDO_ELM_REF = 'referencia';
    private const WS_PEDIDO_ELM_IDREF = 'idReferencia';
    private const WS_PEDIDO_ELM_METPAGO = 'metodosPago';
    private const WS_PEDIDO_ELM_ORIG = 'origen';
    private const WS_PEDIDO_ELM_ORIG_CURMUDLE = 'CURMUDLE';
    private const WS_PEDIDO_ELM_PRODS = 'productos';
    private const WS_PEDIDO_ELM_PRODS_NOM = 'nombre';
    private const WS_PEDIDO_ELM_PRODS_DESC = 'descripcion';
    private const WS_PEDIDO_ELM_PRODS_NOTAS = 'notas';
    private const WS_PEDIDO_ELM_PRODS_PRC = 'precio';
    private const WS_PEDIDO_ELM_PRODS_IVA = 'iva';
    private const WS_PEDIDO_ELM_PRODS_TP = 'codigoTipoProducto';
    private const WS_PEDIDO_ELM_PRODS_TP_CUR = 'CURMUDLE';
    private const WS_PEDIDO_ELM_PRODS_INSTIT = 'institucion';
    private const WS_PEDIDO_ELM_PRODS_CAD = 'caduca';
    private const WS_PEDIDO_ELM_PRODS_ATRBS = 'atributos';
    private const WS_PEDIDO_ELM_PRODS_ATRBS_COD = 'codigo';
    private const WS_PEDIDO_ELM_PRODS_ATRBS_VAL = 'valor';
    private const WS_PEDIDO_ELM_PRODS_ATRBS_IDM = 'idioma';
    private const WS_PEDIDO_ELM_PRODS_INSTS = 'instanciasProducto';
    private const WS_PEDIDO_ELM_PRODS_INSTS_NOM = 'nombre';
    private const WS_PEDIDO_ELM_PRODS_INSTS_PRC = 'precio';
    private const WS_PEDIDO_ELM_PRODS_INSTS_IVA = 'iva';
    private const WS_PEDIDO_ELM_PRODS_INSTS_CANTD = 'cantidad';
    private const WS_PEDIDO_ELM_PRODS_INSTS_ATRBS = 'atributos';
    private const WS_PEDIDO_ELM_PRODS_INSTS_ATRBS_COD = 'codigo';
    private const WS_PEDIDO_ELM_PRODS_INSTS_ATRBS_VAL = 'valor';
    private const WS_PEDIDO_ELM_PRODS_INSTS_ATRBS_IDM = 'idioma';
    private const WS_ATRB_PROD_ID = 'id_ext';
    private const WS_ATRB_USER_ID = 'user_ext';
    private const WS_PEDIDO_ELM_VERSION = 'version';
    private const MAX_LENGTH_INST_PROD_NOM = 255;
    private const MAX_LENGTH_PROD_DESCP = 255;
    
    /**
     * @var string The base API URL
     */
    private $urlbase;
    
    /**
     * @var string Mucommerce user
     */
    private $user;
    
    /**
     * @var string Mucommerce pwd
     */
    private $pwd;
    
    /**
     * helper constructor.
     *
     * @param string $user The mucommerce api user.
     * @param string $secret The mucommerce api pwd.
     * @param bool $sandbox Whether we are working with the sandbox environment or not.
     */
    public function __construct(string $urlbase, string $user, string $pwd, bool $sandbox) {
        $this->urlbase = $urlbase;
        $this->user = $user;
        $this->pwd = $pwd;
    }
    
    /**
     * Captures an authorized payment, by ID.
     *
     * @param float $amount The amount to capture.
     * @param string $currency The currency code for the amount.
     * @param float $surcharge The amount to capture.
     * @return array|null Formatted API response.
     */
    public function make_payment_req(float $amount, float $fee, $course): ?array { //, string $currency
        global $USER, $DB;
        
        if(is_null($course)) return null;
        
        $location = "{$this->urlbase}/payurl";
        
        $options = [
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_TIMEOUT' => 30,
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
            'CURLOPT_SSLVERSION' => CURL_SSLVERSION_TLSv1_2,
            'CURLOPT_HTTPHEADER' => [
                'Content-Type: application/json;charset=UTF-8',
                'Accept: application/json',
            ],
            'CURLOPT_USERPWD' => $this->user.':'.$this->pwd,
        ];
        
        $institucion = null;
        
        $userDni = self::getUserFieldData(UserFields::USER_DB_FIELD_DNI, $USER->id, $DB);
        
        $lengthCourseShortname = (!is_null($course->shortname) && !empty($course->shortname) ? strlen($course->shortname) : 0);
        $lengthUserDni = (!is_null($userDni) && !empty($userDni) ? strlen($userDni) : 0);
        $instProdName = ($lengthCourseShortname > 0 && $lengthUserDni > 0 ? (($lengthCourseShortname + $lengthUserDni) > self::MAX_LENGTH_INST_PROD_NOM ? substr($course->shortname, 0, self::MAX_LENGTH_INST_PROD_NOM - $lengthUserDni - 4).'...'.$userDni :  $course->shortname.' '.$userDni) : '');
        
        $jsondata = [
            self::WS_PEDIDO_ELM_CLIENTE => [
                self::WS_PEDIDO_ELM_CLIENTE_IDENTF => self::getUserFieldData(UserFields::USER_DB_FIELD_INVOICE_CIF, $USER->id, $DB),
                self::WS_PEDIDO_ELM_CLIENTE_NOM => self::getUserFieldData(UserFields::USER_DB_FIELD_INVOICE_NOMBRE, $USER->id, $DB),
                self::WS_PEDIDO_ELM_CLIENTE_DIR => self::getUserFieldData(UserFields::USER_DB_FIELD_INVOICE_DIRECCION, $USER->id, $DB),
                self::WS_PEDIDO_ELM_CLIENTE_CP => self::getUserFieldData(UserFields::USER_DB_FIELD_INVOICE_CP, $USER->id, $DB),
                self::WS_PEDIDO_ELM_CLIENTE_MNCP => self::getUserFieldData(UserFields::USER_DB_FIELD_INVOICE_MUNICIPIO, $USER->id, $DB),
                self::WS_PEDIDO_ELM_CLIENTE_CONTACT => self::getUserFieldData(UserFields::USER_DB_FIELD_INVOICE_NOMBRE, $USER->id, $DB),
                self::WS_PEDIDO_ELM_CLIENTE_MAIL => self::getUserFieldData(UserFields::USER_DB_FIELD_INVOICE_EMAIL, $USER->id, $DB),
                self::WS_PEDIDO_ELM_CLIENTE_TLFN => self::getUserFieldData(UserFields::USER_DB_FIELD_INVOICE_TELEFONO, $USER->id, $DB),
            ],
            self::WS_PEDIDO_ELM_IDM => current_language(),
            self::WS_PEDIDO_ELM_INSTIT => $institucion,
            self::WS_PEDIDO_ELM_CAD=> null,
            self::WS_PEDIDO_ELM_NOTAS => '',
            self::WS_PEDIDO_ELM_REF => '',
            self::WS_PEDIDO_ELM_IDREF => '',
            self::WS_PEDIDO_ELM_METPAGO => 1,
            self::WS_PEDIDO_ELM_ORIG => self::WS_PEDIDO_ELM_ORIG_CURMUDLE,
            self::WS_PEDIDO_ELM_PRODS => [
                [
                    self::WS_PEDIDO_ELM_PRODS_NOM => $course->fullname,
                    self::WS_PEDIDO_ELM_PRODS_DESC => (strlen($course->summary) > self::MAX_LENGTH_PROD_DESCP ? substr($course->summary, 0, self::MAX_LENGTH_PROD_DESCP - 1) : $course->summary),
                    self::WS_PEDIDO_ELM_PRODS_NOTAS => '',
                    self::WS_PEDIDO_ELM_PRODS_PRC => $amount,
                    self::WS_PEDIDO_ELM_PRODS_IVA => $fee,
                    self::WS_PEDIDO_ELM_PRODS_TP => self::WS_PEDIDO_ELM_PRODS_TP_CUR,
                    self::WS_PEDIDO_ELM_PRODS_INSTIT => $institucion,
                    self::WS_PEDIDO_ELM_PRODS_CAD => null,
                    self::WS_PEDIDO_ELM_PRODS_ATRBS => [
                        [
                            self::WS_PEDIDO_ELM_PRODS_ATRBS_COD => self::WS_ATRB_PROD_ID,
                            self::WS_PEDIDO_ELM_PRODS_ATRBS_VAL => $course->id,
                            self::WS_PEDIDO_ELM_PRODS_ATRBS_IDM => null,
                        ],
                    ],
                    self::WS_PEDIDO_ELM_PRODS_INSTS => [
                        [
                            self::WS_PEDIDO_ELM_PRODS_INSTS_NOM => $instProdName,
                            self::WS_PEDIDO_ELM_PRODS_INSTS_PRC => $amount,
                            self::WS_PEDIDO_ELM_PRODS_INSTS_IVA => $fee,
                            self::WS_PEDIDO_ELM_PRODS_INSTS_CANTD => 1,
                            self::WS_PEDIDO_ELM_PRODS_INSTS_ATRBS => [
                                [
                                    self::WS_PEDIDO_ELM_PRODS_INSTS_ATRBS_COD => self::WS_ATRB_PROD_ID,
                                    self::WS_PEDIDO_ELM_PRODS_INSTS_ATRBS_VAL => $course->id,
                                    self::WS_PEDIDO_ELM_PRODS_INSTS_ATRBS_IDM => null,
                                ],
                                [
                                    self::WS_PEDIDO_ELM_PRODS_INSTS_ATRBS_COD => self::WS_ATRB_USER_ID,
                                    self::WS_PEDIDO_ELM_PRODS_INSTS_ATRBS_VAL => $USER->id,
                                    self::WS_PEDIDO_ELM_PRODS_INSTS_ATRBS_IDM => null,
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            self::WS_PEDIDO_ELM_VERSION => '1.0',
        ];
        $jsondata = json_encode($jsondata);
        
        $curl = new curl();
        $result = $curl->post($location, $jsondata, $options);
        
        return json_decode($result, true);
    }
    
    /**
     * Captures order that has been paid by course and user.
     *
     * @param int $courseid The course we want to know had been paid by user.
     * @param int $userid The user we want to know had paid for course.
     * @return int the orderid of mucommerce.
     */
    public function order_paid(int $courseid, int $userid, array $orderIds): ?int {
        $location = "{$this->urlbase}/course/{$courseid}/user/{$userid}/orderstatus";
        
        $ordersParam = null;
        $contOrders = 0;
        if(!is_null($orderIds) && sizeof($orderIds)) {
            $ordersParam = '?ods=';
            foreach ($orderIds as $orderId) {
                if($contOrders > 0) $ordersParam = $ordersParam.',';
                $ordersParam = $ordersParam.$orderId;
                $contOrders++;
            }
        }
        
        if(!is_null($ordersParam) && $contOrders > 0) {
            $location = $location.$ordersParam;
            
            $options = [
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_TIMEOUT' => 30,
                'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
                'CURLOPT_SSLVERSION' => CURL_SSLVERSION_TLSv1_2,
                'CURLOPT_HTTPHEADER' => [
                    'Content-Type: application/json;charset=UTF-8',
                ],
                'CURLOPT_USERPWD' => $this->user.':'.$this->pwd,
            ];
            
            $curl = new curl();
            $result = $curl->get($location, [], $options);
            
            $status = json_decode($result, true);
            
            if($status && array_key_exists('status', $status) && $status['status'] === self::STATUS_COMPLETED && array_key_exists('orderid', $status)) return $status['orderid'];
        }
        
        return -1;
    }
        
    public function getUserFieldData($field, $userId, $DB) {
        try{
            $result = $DB->get_record_sql('SELECT d.data FROM {user_info_data} d join (SELECT id as fieldid FROM {user_info_field} WHERE shortname = :fieldcod) f on d.fieldid = f.fieldid WHERE d.userid = :userid', ['fieldcod' => $field, 'userid' => $userId]);
            return (!is_null($result) && $result !== false ? $result->data : null);
        } catch (\Exception $e) {
            return null;
        }
    }
}