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
 * Adding a new favorites plugin
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package    block_user_favorites
 * @copyright  26-10-2018 MFreak.nl
 * @author     Luuk Verhoeven
 **/

namespace block_user_favorites;

use dml_exception;

/**
 * Class favorites
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  26-10-2018 MFreak.nl
 */
class favorites {

    /**
     * @var int $userid User id
     */
    protected int $userid;

    /**
     * favorites constructor.
     *
     * @param int $userid
     */
    public function __construct(int $userid = 0) {
        global $USER;
        if (empty($userid)) {
            $userid = $USER->id;
        }

        $this->userid = $userid;
    }

    /**
     * Insert/update a user favorite
     *
     * @param \stdClass $favorite
     * @param string $hash
     * @param int $userid
     *
     * @return int
     */
    public function update_favorite(\stdClass $favorite, string $hash, int $userid): int {
        global $DB;

        // Use a single timestamps.
        static $now;
        if (empty($now)) {
            $now = time();
        }

        $favorite->userid = $userid;
        $favorite->hash = $hash;

        if (($row = $DB->get_record('block_user_favorites', ['userid' => $userid, 'hash' => $hash])) !== false) {
            $favorite->id = $row->id;
            $DB->update_record('block_user_favorites', $favorite);

            return $row->id;
        }

        // Save timestamp.
        $favorite->created_at = $now;

        return $DB->insert_record('block_user_favorites', $favorite);
    }

    /**
     * Set an url
     *
     * This function will update if exists or create a new favorite.
     *
     * @param string $url
     * @param string $title
     * @param int $sort
     */
    public function set_by_url(string $url, string $title = '', int $sort = 0): void {
        global $USER;
        $hash = md5($url);

        $this->update_favorite(
            (object) [
                'url' => $url,
                'title' => $title,
                'sortorder' => $sort,
                'hash' => $hash,
            ],
            $hash,
            $USER->id
        );
    }

    /**
     * Set order of favorite.
     *
     * @param string $hash
     * @param int $sortorder
     */
    public function set_order(string $hash, int $sortorder = 0): void {
        global $USER;

        $this->update_favorite(
            (object) [
                'sortorder' => $sortorder,
            ],
            $hash,
            $USER->id
        );
    }

    /**
     * Delete by hash.
     *
     * @param string $hash
     *
     * @throws dml_exception
     */
    public function delete_by_hash(string $hash): void {
        global $DB;
        $DB->delete_records('block_user_favorites', [
            'userid' => $this->userid,
            'hash' => $hash,
        ]);
    }

    /**
     * Get_all favorites.
     *
     * @return array
     * @throws dml_exception
     */
    public function get_all(): array {
        global $DB;

        return $DB->get_records('block_user_favorites', ['userid' => $this->userid], 'sortorder ASC', '*');
    }

    /**
     * Check if it has favorites.
     *
     * @return bool
     */
    public function has_favorites(): bool {
        global $DB;
        $record = $DB->get_records(
            'block_user_favorites',
            ['userid' => $this->userid],
            '',
            'id',
            0,
            1
        );

        return !empty($record);
    }

    /**
     * Update title.
     *
     * @param string $hash
     * @param string $title
     */
    public function set_title(string $hash, string $title): void {

        $this->update_favorite(
            (object) [
                'title' => $title,
            ],
            $hash,
            $this->userid
        );
    }

}
