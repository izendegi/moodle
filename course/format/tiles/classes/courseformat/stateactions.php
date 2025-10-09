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

namespace format_tiles\courseformat;

use core_courseformat\stateupdates;
use core_courseformat\stateactions as stateactions_base;
use format_topics\courseformat\stateactions as stateactions_topics;
use stdClass;

/**
 * Contains the core course state actions specific to tiles format.
 *
 * @package    format_tiles
 * @copyright  2025 David Watson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class stateactions extends stateactions_base {

    /**
     * Highlight course section.
     *
     * @param stateupdates $updates the affected course elements track
     * @param stdClass $course the course object
     * @param int[] $ids section ids (only the first one will be highlighted)
     * @param ?int $targetsectionid not used
     * @param ?int $targetcmid not used
     */
    public function section_highlight(
        stateupdates $updates,
        stdClass     $course,
        array        $ids = [],
        ?int         $targetsectionid = null,
        ?int         $targetcmid = null
    ): void {
        $actions = new stateactions_topics();
        $actions->section_highlight(
            $updates, $course, $ids, $targetsectionid, $targetcmid
        );
    }

    /**
     * Remove highlight from a course section.
     *
     * @param stateupdates $updates the affected course elements track
     * @param stdClass $course the course object
     * @param int[] $ids optional extra section ids to refresh
     * @param ?int $targetsectionid not used
     * @param ?int $targetcmid not used
     */
    public function section_unhighlight(
        stateupdates $updates,
        stdClass $course,
        array $ids = [],
        ?int $targetsectionid = null,
        ?int $targetcmid = null
    ): void {
        $actions = new stateactions_topics();
        $actions->section_unhighlight(
            $updates, $course, $ids, $targetsectionid, $targetcmid
        );
    }
}
