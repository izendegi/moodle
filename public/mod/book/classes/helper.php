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
namespace mod_book;

/**
 * Book helper
 *
 * @package    mod_book
 * @copyright  2023 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Check if the user completed the book read based on its read percent requirement
     *
     * @param int $bookid
     * @param int $userid
     * @return bool
     */
    public static function is_book_read_completed($bookid, $userid): bool {
        global $DB;

        $book = $DB->get_record('book', ['id' => $bookid], '*', MUST_EXIST);

        if (!$book->readpercent) {
            return false;
        }

        $percentviewed = self::get_book_userview_progress($book->id, $userid);

        if ($percentviewed >= $book->readpercent) {
            return true;
        }

        return false;
    }

    /**
     * Returns the user progress in a book based on their userviews
     *
     * @param int $bookid
     * @param int $userid
     * @return int
     */
    public static function get_book_userview_progress($bookid, $userid): int {
        global $DB;

        $chapters = $DB->get_records('book_chapters', ['bookid' => $bookid, 'hidden' => 0], 'id', 'id');

        $userviewedchapters = self::get_book_userviews($bookid, $userid);

        if (!$chapters || !$userviewedchapters) {
            return 0;
        }

        return (int)((count($userviewedchapters) / count($chapters)) * 100);
    }

    /**
     * Returns all chapters views of a user.
     *
     * @param int $bookid
     * @param int $userid
     * @return array|bool
     */
    public static function get_book_userviews($bookid, $userid): array|bool {
        global $DB;

        $userviewedchapterssql = "SELECT DISTINCT uv.chapterid
                              FROM {book_chapters_userviews} uv
                              INNER JOIN {book_chapters} bc ON bc.id = uv.chapterid
                              INNER JOIN {book} b ON b.id = bc.bookid
                              WHERE bc.bookid = :bookid AND uv.userid = :userid AND bc.hidden = 0";
        $parameters = [
            'bookid' => $bookid,
            'userid' => $userid,
        ];

        $userviewedchapters = $DB->get_records_sql($userviewedchapterssql, $parameters);

        if ($userviewedchapters) {
            return $userviewedchapters;
        }

        return false;
    }
}
