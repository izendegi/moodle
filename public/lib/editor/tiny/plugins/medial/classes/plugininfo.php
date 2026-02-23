<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Tiny MEDIAL Plugin plugin for Moodle.
 *
 * @package     tiny_medial
 * @copyright   2023 MEDIAL, Tim Williams <tim@medial.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_medial;

use context;
use editor_tiny\editor;
use editor_tiny\plugin;
use editor_tiny\plugin_with_buttons;
use editor_tiny\plugin_with_menuitems;
use editor_tiny\plugin_with_configuration;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/editor/tiny/plugins/medial/lib.php');
require_once($CFG->dirroot . '/mod/helixmedia/locallib.php');

/**
 * Tiny MEDIAL plugin.
 *
 * @package    tiny_medial
 * @copyright  2024 MEDIAL Tim Williams <tim@medial.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugininfo extends plugin implements plugin_with_buttons, plugin_with_configuration, plugin_with_menuitems {
    /**
     * Whether the plugin is enabled
     *
     * @param context $context The context that the editor is used within
     * @param array $options The options passed in when requesting the editor
     * @param array $fpoptions The filepicker options passed in when requesting the editor
     * @param ?editor $editor The editor instance in which the plugin is initialised
     * @return boolean
     */
    public static function is_enabled(
        context $context,
        array $options,
        array $fpoptions,
        ?editor $editor = null
    ): bool {
        global $PAGE, $DB;
        // Disabled if:
        // - The user doesn't have the correct permissions
        // - Files are not allowed.
        // - Only URL are supported.
        $canhavefiles = !empty($options['maxfiles']);
        $canhaveexternalfiles = !empty($options['return_types']) && ($options['return_types'] & FILE_EXTERNAL);

        $mtype = tiny_medial_checklist('modtypeperm');
        $permission = false;
        if ($mtype) {
            if (has_capability('tiny/medial:visiblemodtype', $context)) {
                $permission = true;
            }
        } else {
            if (has_capability('tiny/medial:visible', $context)) {
                $permission = true;
            }
        }

        if ($permission && !$canhaveexternalfiles && $canhavefiles) {
            // Special case, this has been added to deal with the use of MEDIAL in the Essay question type.
            // Essay questions have no response format option that allows external files, so if we are in
            // a context where the user has permission, internal files are allowed but external files
            // are not allowed, we may want to use allow MEDIAL to work as an internal file instead.

            $internal = get_config('tiny_medial', 'internal');
            if ($internal == 1) {
                $canhaveexternalfiles = $canhavefiles;
            }
        }

        $func = true;
        // This hides the button in specific situations where we want people to use the plugin, eg in the MEDIAL activity
        // and the assign grading interface where we want people to use Feedback.
        if ($PAGE->cm) {
            switch ($PAGE->cm->modname) {
                case "helixmedia":
                    $func = false;
                    break;
                case "assign":
                    $action = $PAGE->url->get_param("action");
                    if (($action == "grader" || $action == "grade" ) && !get_config('tiny_medial', 'allowfeedback')) {
                        $func = false;
                    }
                    break;
            }
        } else {
            // Object $PAGE->cm is false when adding a new instance, so check the URL and see if we're adding a MEDIAL activity.
            if ($PAGE->url->get_path() == "/course/modedit.php" && $PAGE->url->get_param("add") == "helixmedia") {
                $func = false;
            }
        }

        return $func && $permission && $canhavefiles && $canhaveexternalfiles;
    }

    /**
     * Gets the available buttons provided by this plugin
     * @return array of buttons
     **/
    public static function get_available_buttons(): array {
        return [
            'tiny_medial/plugin',
        ];
    }

    /**
     * Gets the available menu items provided by this plugin
     * @return array of menu items
     **/
    public static function get_available_menuitems(): array {
        return [
            'tiny_medial/plugin',
        ];
    }

    /**
     * Get a list of the menu items provided by this plugin.
     *
     * @param context $context The context that the editor is used within
     * @param array $options The options passed in when requesting the editor
     * @param array $fpoptions The filepicker options passed in when requesting the editor
     * @param ?\editor_tiny\editor $editor The editor instance in which the plugin is initialised
     * @return array
     */
    public static function get_plugin_configuration_for_context(
        context $context,
        array $options,
        array $fpoptions,
        ?\editor_tiny\editor $editor = null
    ): array {
        global $USER, $COURSE, $CFG;

        // Switch of button when using the activity module.
        // Use PARAM_RAW type here in case "add" is used for something other than a plugin name in other parts of moodle.
        $add = optional_param("add", "none", PARAM_RAW);
        $action = optional_param("action", "none", PARAM_RAW);
        $linkonly = false;
        $modtype = '';

        $modaldelay = intval(get_config('helixmedia', 'modal_delay'));
        if ($modaldelay > -1) {
            $hideinsert = boolval(get_config('tiny_medial', 'hideinsert'));
        } else {
            $hideinsert = false;
        }
        $hasfilter = boolval(tiny_medial_has_filter($context));
        if ($hasfilter) {
            $placeholder = boolval(get_config('tiny_medial', 'placeholder'));
            $embedopt = boolval(get_config('tiny_medial', 'embedopt'));
        } else {
            $placeholder = false;
            $embedopt = false;
        }

        if ($placeholder || tiny_medial_checklist('uselinkdesc')) {
            $linkonly = true;
        }

        $mtype = tiny_medial_checklist('modtypeperm');
        if ($mtype) {
            if (has_capability('tiny/medial:visiblemodtype', $context)) {
                $modtype = $mtype;
                $linkonly = true;
            }
        }

        if ($hasfilter && has_capability('tiny/medial:addliblink', $context)) {
            $ll = HML_LAUNCH_LIB_ONLY;
        } else {
            $ll = -1;
        }

        if (get_config('helixmedia', 'ltiversion') == LTI_VERSION_1) {
            $oauthkey = get_config('helixmedia', 'consumer_key');
        } else {
            $oauthkey = false;
        }

        return [
            'baseurl' => $CFG->wwwroot,
            'ltiurl' => helixmedial_launch_url(),
            'statusurl' => helixmedia_get_status_url(),
            'userid' => intval($USER->id),
            'hideinsert' => $hideinsert,
            'insertdelay' => $modaldelay,
            'oauthConsumerKey' => $oauthkey,
            'modtype' => $modtype,
            'placeholder' => $placeholder,
            'playersizeurl' => helixmedia_get_playerwidthurl(),
            'course' => intval($COURSE->id),
            'launchType' => HML_LAUNCH_TINYMCE_EDIT,
            'viewLaunchType' => HML_LAUNCH_TINYMCE_VIEW,
            'libLaunchType' => $ll,
            'embedopt' => $embedopt,
            'linkonly' => $linkonly,
            'bs5' => helixmedia_is_moodle_5(),
        ];
    }
}
