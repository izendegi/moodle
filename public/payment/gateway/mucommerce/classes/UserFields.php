<?php

namespace paygw_mucommerce;

class UserFields {
    const USER_FIELD_DNI = 'profile_field_'.self::USER_DB_FIELD_DNI;
    const USER_FIELD_NOMBRE = 'firstname';
    const USER_FIELD_APELLIDOS = 'lastname';
    const USER_FIELD_DIRECCION = 'address';
    const USER_FIELD_MUNICIPIO = 'city';
    const USER_FIELD_CP = 'profile_field_'.self::USER_DB_FIELD_CP;
    const USER_FIELD_EMAIL = 'email';
    const USER_FIELD_TELEFONO1 = 'phone1';
    const USER_FIELD_TELEFONO2 = 'phone2';
    const USER_FIELD_INVOICE_CIF = 'profile_field_'.self::USER_DB_FIELD_INVOICE_CIF;
    const USER_FIELD_INVOICE_NOMBRE = 'profile_field_'.self::USER_DB_FIELD_INVOICE_NOMBRE;
    const USER_FIELD_INVOICE_DIRECCION = 'profile_field_'.self::USER_DB_FIELD_INVOICE_DIRECCION;
    const USER_FIELD_INVOICE_MUNICIPIO = 'profile_field_'.self::USER_DB_FIELD_INVOICE_MUNICIPIO;
    const USER_FIELD_INVOICE_CP = 'profile_field_'.self::USER_DB_FIELD_INVOICE_CP;
    const USER_FIELD_INVOICE_EMAIL = 'profile_field_'.self::USER_DB_FIELD_INVOICE_EMAIL;
    const USER_FIELD_INVOICE_TELEFONO = 'profile_field_'.self::USER_DB_FIELD_INVOICE_TELEFONO;
    
    const USER_FIELDS = array(
        self::USER_FIELD_DNI,
        self::USER_FIELD_NOMBRE,
        self::USER_FIELD_APELLIDOS,
        self::USER_FIELD_DIRECCION,
        self::USER_FIELD_MUNICIPIO,
        self::USER_FIELD_CP,
        self::USER_FIELD_EMAIL,
        self::USER_FIELD_TELEFONO1,
        self::USER_FIELD_TELEFONO2
    );
    const USER_INVOICE_FIELDS = array(
        self::USER_FIELD_INVOICE_CIF,
        self::USER_FIELD_INVOICE_NOMBRE,
        self::USER_FIELD_INVOICE_DIRECCION,
        self::USER_FIELD_INVOICE_MUNICIPIO,
        self::USER_FIELD_INVOICE_CP,
        self::USER_FIELD_INVOICE_EMAIL,
        self::USER_FIELD_INVOICE_TELEFONO
    );
    
    const USER_DB_FIELD_DNI = 'DNI';
    const USER_DB_FIELD_CP = 'CP';
    const USER_DB_FIELD_INVOICE_CIF = 'BILLING_CIF';
    const USER_DB_FIELD_INVOICE_NOMBRE = 'BILLING_NAME';
    const USER_DB_FIELD_INVOICE_DIRECCION = 'BILLING_ADDRESS';
    const USER_DB_FIELD_INVOICE_MUNICIPIO = 'BILLING_CITY';
    const USER_DB_FIELD_INVOICE_CP = 'BILLING_CP';
    const USER_DB_FIELD_INVOICE_EMAIL = 'BILLING_EMAIL';
    const USER_DB_FIELD_INVOICE_TELEFONO = 'BILLING_PHONE';
    
    public static function getAllUserFields() {
        return array_merge(
            self::USER_FIELDS,
            self::USER_INVOICE_FIELDS
        );
    }
}