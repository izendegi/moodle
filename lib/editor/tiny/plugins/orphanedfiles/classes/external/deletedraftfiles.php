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

namespace tiny_orphanedfiles\external;

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/externallib.php");
require_once($CFG->dirroot . '/repository/lib.php');

use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;

/**
 * Defines a Webservice to delete draftfiles in a draftarea
 *
 * @package    tiny_orphanedfiles
 * @copyright  2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @copyright  2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @author     2023 Andreas Siebel <andreas.siebel@schulportal.hessen.de>
 * @author     2023 Andreas Schenkel <andreas.schenkel@schulportal.hessen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deletedraftfiles extends \external_api {
    /** Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
                'draftItemId' => new external_value(PARAM_INT, 'id of draftarea'),
                'files' => new external_multiple_structure(
                        new external_single_structure([
                                'filepath' => new external_value(PARAM_PATH, 'filepath'),
                                'filename' => new external_value(PARAM_FILE, 'filename'),
                        ])
                ),
        ]);
    }

    /** Describes the data returned from the external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
                ['directories' => new external_value(PARAM_TEXT,
                        'List of parent directories in which files have been deleted encoded as json string'),
                ]
        );
    }

    /** External function to find all files in draftarea of the editor specified by draftitemid in editorconfig.
     *
     * @param int $draftitemid The draftitemid of draftarea
     * @param array $files A list of files to be deleted
     * @return array An array with format: [directories->json encoded string]
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \restricted_context_exception
     */
    public static function execute($draftitemid, $files): array {
        global $USER;
        ['draftItemId' => $draftitemid, 'files' => $files] =
                self::validate_parameters(self::execute_parameters(), ['draftItemId' => $draftitemid, 'files' => $files]);
        $usercontext = \context_user::instance($USER->id);
        self::validate_context($usercontext);

        // The parameter $files has not the correct structure so wie need to generate $fileObjects from $files.
        $fileobjects = [];
        foreach ($files as $file) {
            $fileobject = (object) [
                    'filename' => $file['filename'],
                    'filepath' => $file['filepath'],
            ];
            $fileobjects[] = $fileobject;
        }

        $parentdirectories = repository_delete_selected_files($usercontext, 'user', 'draft', $draftitemid, $fileobjects);
        return ["directories" => json_encode($parentdirectories)];
    }
}
