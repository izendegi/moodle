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
 * Lib.
 *
 * @package    mod_structlabel
 * @copyright  2019 Coventry University
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Whether the module supportes a certain feature.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know.
 */
function mod_structlabel_supports($feature) {
    switch($feature) {
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GRADE_OUTCOMES:
        case FEATURE_GROUPINGS:
        case FEATURE_GROUPS:
        case FEATURE_SHOW_DESCRIPTION:
            return false;

        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_IDNUMBER:
        case FEATURE_MOD_INTRO:
        case FEATURE_NO_VIEW_LINK:
            return true;

        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;

        default:
            return null;
    }
}

/**
 * Add instance.
 *
 * @param stdClass $instance The instance.
 * @param object $mform The form.
 * @return int
 */
function structlabel_add_instance($instance, $mform) {
    global $DB;

    $cmid = $instance->coursemodule;
    $draftitemid = $instance->image;
    unset($instance->image);

    $id = $DB->insert_record('structlabel', $instance);

    // Save the resources.
    if (!empty($instance->resources)) {
        foreach ($instance->resources as $resource) {
            $resource->structlabelid = $id;
            $DB->insert_record('structlabel_resources', $resource);
        }
    }

    // Save the image.
    if (!empty($draftitemid)) {
        $fs = get_file_storage();
        $context = context_module::instance($cmid);
        $options = mod_structlabel_image_filemanager_options();
        file_save_draft_area_files($draftitemid, $context->id, 'mod_structlabel', 'image', 0, $options);
    }

    return $id;
}

/**
 * Update instance.
 *
 * @param stdClass $instance The instance.
 * @param object $mform The form.
 * @return bool
 */
function structlabel_update_instance($instance, $mform) {
    global $DB;

    $id = $instance->instance;
    $cmid = $instance->coursemodule;
    $draftitemid = $instance->image;
    unset($instance->image);

    $instance->id = $id;
    $success = $DB->update_record('structlabel', $instance);
    if (!$success) {
        return false;
    }

    // Save the resources.
    $DB->delete_records('structlabel_resources', ['structlabelid' => $instance->id]);
    if (!empty($instance->resources)) {
        foreach ($instance->resources as $resource) {
            $resource->structlabelid = $id;
            $DB->insert_record('structlabel_resources', $resource);
        }
    }

    // Save the image.
    if ($success && !empty($draftitemid)) {
        $fs = get_file_storage();
        $context = context_module::instance($cmid);
        $options = mod_structlabel_image_filemanager_options();
        file_save_draft_area_files($draftitemid, $context->id, 'mod_structlabel', 'image', 0, $options);
    }

    return $success;
}


/**
 * Delete instance.
 *
 * @param int $id The ID.
 * @return bool
 */
function structlabel_delete_instance($id) {
    global $DB;

    // Note that all context files are deleted by core.
    $DB->delete_records('structlabel_resources', ['structlabelid' => $id]);
    $DB->delete_records('structlabel', ['id' => $id]);

    return true;
}

/**
 * Cache course module info for course page display.
 *
 * @param stdClass $cm The CM record.
 * @return cached_cm_info Cached information.
 */
function structlabel_get_coursemodule_info($cm) {
    global $DB;

    $params = ['id' => $cm->instance];
    if (!$record = $DB->get_record('structlabel', $params, 'id, name, intro, introformat')) {
        return false;
    }

    $context = context_module::instance($cm->id);

    $result = new cached_cm_info();
    $result->name = $record->name;
    $result->customdata = new stdClass();
    $result->customdata->intro = $record->intro;
    $result->customdata->introformat = $record->introformat;

    // Find the resources.
    $resources = $DB->get_records('structlabel_resources', ['structlabelid' => $cm->instance], 'id');
    $result->customdata->resources = array_values(array_map(function($resource) {
        return (object) ['url' => $resource->url, 'text' => $resource->text, 'icon' => $resource->icon];
    }, $resources));

    // Find the image, and store its details.
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_structlabel', 'image', 0, '', false);
    $file = reset($files);
    if (!empty($file)) {
        $result->customdata->image = (object) [
            'filename' => $file->get_filename(),
            'timemodified' => $file->get_timemodified()
        ];
    }

    return $result;
}

/**
 * Content to display on the course page.
 *
 * @param cm_info $cm The CM info.
 */
function mod_structlabel_cm_info_view(cm_info $cm) {
    global $PAGE;

    if (!$cm->uservisible) {
        return;
    }

    $renderer = $PAGE->get_renderer('mod_structlabel');
    $cm->set_content($renderer->display_content($cm), true);
}

/**
 * File serving function.
 *
 * @param object $course The course.
 * @param object $cm The course module.
 * @param context $context The context.
 * @param string $filearea The file area.
 * @param array $args The arguments.
 * @param bool $forcedownload Whether to force the download.
 * @param array $options The options.
 * @return bool|void
 */
function mod_structlabel_pluginfile($course, $cm, context $context, $filearea, array $args, $forcedownload, array $options = []) {
    global $CFG;
    $fs = get_file_storage();

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);
    if (!has_capability('mod/structlabel:view', $context)) {
        return false;
    }

    if ($filearea === 'image') {
        $lifetime = DAYSECS * 120;
        $filename = array_pop($args);
        $timemodified = array_pop($args);

        // Attempt to find the cropped version.
        $width = get_config('mod_structlabel', 'imagewidth');
        $height = get_config('mod_structlabel', 'imageheight');
        $croppedfilepath = "/$timemodified/$width/$height/";

        // Check if we've got a cached file to return.
        $candidate = $CFG->localcachedir . "/mod_structlabel/{$context->id}/$filearea/image/{$width}x{$height}/$filename";
        if (file_exists($candidate)) {
            send_file($candidate, $filename, $lifetime, 0, false, false, '', false, $options);
        }

        // We didn't find a cropped version.
        $file = $fs->get_file($context->id, 'mod_structlabel', 'image', 0, '/', $filename);
        if (!$file) {
            return false;
        }

        // No need for resizing, but if the file should be cached we save it so we can serve it fast next time.
        if (empty($width) && empty($height)) {
            file_safe_save_content($file->get_content(), $candidate);
            send_stored_file($file, $lifetime, 0, false, $options);
        }

        // Proceed with the resizing.
        $filedata = mod_structlabel_resize_file($file, $width, $height);
        if (!$filedata) {
            send_file_not_found();
        }

        // Save, serve and quit.
        file_safe_save_content($filedata, $candidate);
        send_file($candidate, $filename, $lifetime, 0, false, false, '', false, $options);
    }

    return false;
}

/**
 * Get the image file manager options.
 *
 * @return array
 */
function mod_structlabel_image_filemanager_options() {
    return ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['image/jpeg', 'image/png', 'image/gif']];
}

/**
 * Parse resources styles from a string.
 *
 * Note that this does not validate anything, and may return empty lines or invalid styles.
 *
 * @param string $value The value.
 * @return array
 */
function mod_structlabel_parse_resource_styles($value) {
    $lines = explode("\n", $value);
    return array_map(function($line) {
        $line = trim($line);
        $parts = explode('|', $line, 3);
        if (empty($parts[0])) {
            return null;
        }
        return (object) [
            'url' => $parts[0],
            'colour' => !empty($parts[1]) ? $parts[1] : null,
            'icon' => !empty($parts[2]) ? $parts[2] : null
        ];
    }, $lines);
}

/**
 * Resize a file.
 *
 * When both the width and height are provided, the image will be cropped
 * to enforce both the dimensions.
 *
 * Logic based off {@link stored_file::resize_image}.
 *
 * @param stored_file $file The file.
 * @param int $width The width.
 * @param int $height The height.
 * @return string|false The binary content.
 */
function mod_structlabel_resize_file(stored_file $file, $width, $height) {
    global $CFG;
    require_once($CFG->libdir . '/gdlib.php');

    if (!$width && !$height) {
        return false;
    }

    // Fetch the image information for this image.
    $content = $file->get_content();
    $imageinfo = @getimagesizefromstring($content);
    if (empty($imageinfo)) {
        return false;
    }

    // Create a new image from the file.
    $original = @imagecreatefromstring($content);

    // Generate the resized image.
    if (!empty($height) && !empty($width)) {
        return mod_structlabel_crop_image($original, $imageinfo, $width, $height);
    }
    return resize_image_from_image($original, $imageinfo, $width, $height);
}

/**
 * Resize an image from an image object.
 *
 * Adapted from {@link resize_image_from_image}.
 *
 * @param resource $original The image to work on.
 * @param array $imageinfo Contains [0] => originalwidth, [1] => originalheight.
 * @param int $width The width of the resized image.
 * @param int $height The height of the resized image.
 * @return string|bool False if a problem occurs, else the resized image data.
 */
function mod_structlabel_crop_image($original, $imageinfo, $width, $height) {
    global $CFG;

    $originalwidth  = $imageinfo[0];
    $originalheight = $imageinfo[1];

    if (empty($width) || empty($height)) {
        return false;
    } else if (empty($imageinfo)) {
        return false;
    } else if (empty($originalwidth) or empty($originalheight)) {
        return false;
    }

    if (function_exists('imagepng')) {
        $imagefnc = 'imagepng';
        $filters = PNG_NO_FILTER;
        $quality = 1;
    } else if (function_exists('imagejpeg')) {
        $imagefnc = 'imagejpeg';
        $filters = null;
        $quality = 90;
    } else {
        debugging('Neither JPEG nor PNG are supported at this server, please fix the system configuration.');
        return false;
    }

    $widthratio = $width / $originalwidth;
    $heightratio = $height / $originalheight;
    $revisedwidth = $originalwidth;
    $revisedheight = $originalheight;
    $offsetx = 0;
    $offsety = 0;

    if ($heightratio > $widthratio) {
        $ratio = $width / $height;
        $revisedwidth = floor($originalheight * $ratio);
        $offsetx = floor(($originalwidth - $revisedwidth) / 2);
    } else if ($widthratio > $heightratio) {
        $ratio = $height / $width;
        $revisedheight = floor($originalwidth * $ratio);
        $offsety = floor(($originalheight - $revisedheight) / 2);
    }

    if (function_exists('imagecreatetruecolor')) {
        $newimage = imagecreatetruecolor($width, $height);
        if ($imagefnc === 'imagepng') {
            imagealphablending($newimage, false);
            imagefill($newimage, 0, 0, imagecolorallocatealpha($newimage, 0, 0, 0, 127));
            imagesavealpha($newimage, true);
        }
    } else {
        $newimage = imagecreate($width, $height);
    }

    imagecopybicubic($newimage, $original, 0, 0, $offsetx, $offsety, $width, $height, $revisedwidth, $revisedheight);

    // Capture the image as a string object, rather than straight to file.
    ob_start();
    if (!$imagefnc($newimage, null, $quality, $filters)) {
        ob_end_clean();
        return false;
    }
    $data = ob_get_clean();
    imagedestroy($original);
    imagedestroy($newimage);

    return $data;
}
