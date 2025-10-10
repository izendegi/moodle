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
 * The Filter Gitquotes Main Class.
 *
 * @package   filter_gitquotes
 * @copyright 2024 devrdn rrdninc@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The filter_gitquotes class.
 *
 * This class extends the moodle_text_filter class and is used to
 * filter the text and replace the custom quotes with the git-style quotes.
 */
namespace filter_gitquotes;

defined('MOODLE_INTERNAL') || die;

if (class_exists('\core_filters\text_filter')) {
    class_alias('\core_filters\text_filter', 'filter_gitquotes_base_text_filter');
} else {
    class_alias('\moodle_text_filter', 'filter_gitquotes_base_text_filter');
}

class text_filter extends \filter_gitquotes_base_text_filter {
    /**
     * The pattern for the custom quotes.
     */
    public const PATTERN_HTML = '/<blockquote>\s*?<p>\s*\[\!(NOTE|WARNING|TIP|IMPORTANT)\](.*?)<\/p>\s*<\/blockquote>/is';

    /**
     * Filter function.
     * Filters the text and replaces the custom quotes with the git-style quotes.
     *
     * @param string $text The text to be filtered.
     * @param array $options The options for the filter.
     */
    public function filter($text, array $options = []) {
        if (empty($text)) {
            return $text;
        }

        // Get the format of the text.
        $format = "";
        if (isset($options['context']) && method_exists($options['context'], 'get_format')) {
            $format = $options['context']->get_format();
        } else {
            $format = FORMAT_MARKDOWN;
        }

        // If the format is not markdown, return the text as it is.
        if (!in_array($format, [FORMAT_MARKDOWN])) {
            return $text;
        }

        // Replace the custom quotes with the git-style quotes.
        $replacementhtml = '<blockquote class="gitquote $1"><div class="gitquote_name">';
        $replacementhtml .= '<strong class="quote-type $1">$1</strong></div><span class="quote-content">$2</span></blockquote>';

        $text = preg_replace(self::PATTERN_HTML, $replacementhtml, $text);

        return $text;
    }
}
