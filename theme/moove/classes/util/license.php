<?php

namespace theme_moove\util;

use core\notification;

class license {
    const PLUGINSTORE = 'https://pluginstore.conecti.me/api/licenses/verify';
    const STATUS = 'license_status';
    const EXPIRES = 'license_expires_at';

    public function validate_license($key) {
        global $CFG;

        $postdata = [
            'key' => $key,
            'item' => 'theme_moove',
            'site' => urlencode($CFG->wwwroot)
        ];

        $licensedata = $this->fetch_license($postdata);

        if ($licensedata['statuscode'] != 200) {
            set_config(license::STATUS, 'invalid', 'theme_moove');
            unset_config(license::EXPIRES, 'theme_moove');

            $message = get_string_manager()->get_string($licensedata['data'] . '_msg', 'theme_moove');

            notification::error($message);
        }

        if ($licensedata['statuscode'] == 200) {
            set_config(license::STATUS, 'active', 'theme_moove');
            set_config(license::EXPIRES, $licensedata['data'],'theme_moove');

            $message = get_string_manager()->get_string('active_msg', 'theme_moove');

            notification::success($message);
        }
    }

    protected function fetch_license($postdata) {
        $ch = curl_init(license::PLUGINSTORE);

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

        $contents = curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            'statuscode' => $httpcode,
            'data' => $contents
        ];
    }

    public function get_license_status() {
        $status = get_config('theme_moove', license::STATUS);
        $expires = get_config('theme_moove', license::EXPIRES);

        if (!$status || $expires === null) {
            return 'invalid';
        }

        if ($expires !== null && ($expires === '0' && $status == 'active')) {
            return 'active';
        }

        if ($status == 'invalid') {
            return 'invalid';
        }

        if ($expires < time()) {
            return 'expired';
        }

        if ($expires > time() && $status == 'active') {
            return 'active';
        }

        return 'invalid';
    }

    public function get_license_status_badge() {
        $status = $this->get_license_status();

        if ($status == 'invalid') {
            return '<p class="badge badge-danger text-white">'.get_string('invalid', 'theme_moove').'</p>';
        }

        if ($status == 'expired') {
            return '<p class="badge badge-warning text-white">'.get_string('expired', 'theme_moove').'</p>';
        }

        if ($status == 'active') {
            return '<p class="badge badge-success text-white">'.get_string('active', 'theme_moove').'</p>';
        }

        return '<p class="badge badge-danger text-white">'.get_string('invalid', 'theme_moove').'</p>';
    }

    public function is_active() {
        $status = $this->get_license_status();

        return $status === 'active';
    }

    public function force_activation($key, $expires = 0)
    {
        set_config('licensekey', $key, 'theme_moove');
        set_config(license::STATUS, 'active', 'theme_moove');
        set_config(license::EXPIRES, $expires,'theme_moove');
    }
}
