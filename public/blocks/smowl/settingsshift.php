<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Create and redirect to Settings URL with permission management.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot. '/blocks/smowl/lib.php');

$action = optional_param('action', '', PARAM_ALPHA); // Course ID.

require_login();

$siteadmin = has_capability('moodle/site:config', context_system::instance());

if (!$siteadmin) {
    $message = get_string('nocapabilityconfigmessage', 'block_smowl');
    redirect($CFG->wwwroot.'/?redirect=0', $message, null, \core\output\notification::NOTIFY_INFO);
}

switch ($action) {
    case 'manual':
        $typeconfig = get_config('block_smowl', 'typeconfig');
        $entity = get_config('block_smowl', 'entity');
        $password = get_config('block_smowl', 'password');
        $apikey = get_config('block_smowl', 'apikey');
        $jwtsecret = get_config('block_smowl', 'smowljwtsecret');
        if (strcasecmp($entity, '') != 0 &&
            strcasecmp($password, '') != 0 &&
            strcasecmp($apikey, '') != 0 &&
            strcasecmp($jwtsecret, '') != 0)
            {
            set_config('entityprev', $entity, 'block_smowl');
            set_config('passwordprev', $password, 'block_smowl');
            set_config('apikeyprev', $apikey, 'block_smowl');
            set_config('smowljwtsecretprev', $jwtsecret, 'block_smowl');
            set_config('typeconfigprev', $typeconfig, 'block_smowl');
            $clientid = get_config('block_smowl', 'clientid');
            set_config('clientidprev', $clientid, 'block_smowl');
            $clientkey = get_config('block_smowl', 'clientkey');
            set_config('clientkeyprev', $clientkey, 'block_smowl');

            set_config('clientid', '', 'block_smowl');
            set_config('clientkey', '', 'block_smowl');
            set_config('entity', '', 'block_smowl');
            set_config('password', '', 'block_smowl');
            set_config('apikey', '', 'block_smowl');
            set_config('smowljwtsecret', '', 'block_smowl');

        } else {
            set_config('clientid', '', 'block_smowl');
            set_config('clientkey', '', 'block_smowl');
        }
        set_config('typeconfig', 2, 'block_smowl');
        redirect($CFG->wwwroot.'/admin/settings.php?section=blocksettingsmowl');
    break;

    case 'auto':
        $typeconfig = get_config('block_smowl', 'typeconfig');
        $entity = get_config('block_smowl', 'entity');
        $password = get_config('block_smowl', 'password');
        $apikey = get_config('block_smowl', 'apikey');
        $jwtsecret = get_config('block_smowl', 'smowljwtsecret');

        if (strcasecmp($entity, '') != 0 &&
            strcasecmp($password, '') != 0 &&
            strcasecmp($apikey, '') != 0 &&
            strcasecmp($jwtsecret, '') != 0) {
            set_config('entityprev', $entity, 'block_smowl');
            set_config('passwordprev', $password, 'block_smowl');
            set_config('apikeyprev', $apikey, 'block_smowl');
            set_config('smowljwtsecretprev', $jwtsecret, 'block_smowl');
            set_config('clientvalidatedprev', 1, 'block_smowl');
            set_config('typeconfigprev', $typeconfig, 'block_smowl');

        }
        set_config('entity', '', 'block_smowl');
        set_config('password', '', 'block_smowl');
        set_config('apikey', '', 'block_smowl');
        set_config('smowljwtscret', '', 'block_smowl');
        set_config('clientvalidated', null, 'block_smowl');

        if ($typeconfig == 1) {
            $clientid = get_config('block_smowl', 'clientid');
            $clientkey = get_config('block_smowl', 'clientkey');
            if (strcasecmp($clientid, '') != 0 &&
                strcasecmp($clientkey, '') != 0) {
                set_config('clientidprev', $clientid, 'block_smowl');
                set_config('clientkeyprev', $clientkey, 'block_smowl');
                set_config('typeconfigprev', $typeconfig, 'block_smowl');
            }
            set_config('clientid', '', 'block_smowl');
            set_config('clientkey', '', 'block_smowl');
        }
        set_config('typeconfig', 1, 'block_smowl');
        redirect($CFG->wwwroot.'/admin/settings.php?section=blocksettingsmowl');
        break;
    case 'cancel':
        $typeconfigprev = get_config('block_smowl', 'typeconfigprev');
        if ($typeconfigprev) {
            set_config('typeconfig', $typeconfigprev, 'block_smowl');
            set_config('typeconfigprev', null, 'block_smowl');
        }
        $entityprev = get_config('block_smowl', 'entityprev');
        if ($entityprev) {
            set_config('entity', $entityprev, 'block_smowl');
            set_config('entityprev', null, 'block_smowl');
        }
        $passwordprev = get_config('block_smowl', 'passwordprev');
        if ($passwordprev) {
            set_config('password', $passwordprev, 'block_smowl');
            set_config('passwordprev', null, 'block_smowl');
        }
        $apikeyprev = get_config('block_smowl', 'apikeyprev');
        if ($apikeyprev) {
            set_config('apikey', $apikeyprev, 'block_smowl');
            set_config('apikeyprev', null, 'block_smowl');
        }
        $smowljwtsecretprev = get_config('block_smowl', 'smowljwtsecretprev');
        if ($smowljwtsecretprev) {
            set_config('smowljwtsecret', $smowljwtsecretprev, 'block_smowl');
            set_config('smowljwtsecretprev', null, 'block_smowl');
        }
        $clientidprev = get_config('block_smowl', 'clientidprev');
        if ($clientidprev) {
            set_config('clientid', $clientidprev, 'block_smowl');
            set_config('clientidprev', null, 'block_smowl');
        }
        $clientkeyprev = get_config('block_smowl', 'clientkeyprev');
        if ($clientkeyprev) {
            set_config('clientkey', $clientkeyprev, 'block_smowl');
            set_config('clientkeyprev', null, 'block_smowl');
        }
        $clientvalidatedprev = get_config('block_smowl', 'clientvalidatedprev');
        if ($clientvalidatedprev) {
            set_config('clientvalidated', $clientvalidatedprev, 'block_smowl');
            set_config('clientvalidatedprev', null, 'block_smowl');
        }

        redirect($CFG->wwwroot.'/admin/settings.php?section=blocksettingsmowl');
        break;
    case 'after':
    default:
        break;
}
redirect($CFG->wwwroot.'/?redirect=0');
