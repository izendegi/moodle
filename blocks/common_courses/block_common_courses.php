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
 * With this block installed, the courses that are enrolled and we are enrolled are shown in the profile of a user.
 *
 * @package    block_common_courses
 * @copyright  2018 Sergio Comerón Sánchez-Paniagua
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_common_courses extends block_list {

    /**
     * @var bool Flag to indicate whether the header should be hidden or not.
     */
    private $headerhidden = true;

    /**
     * Init function
     *
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_common_courses');
    }

    /**
     * applicable_formats function
     *
     */
    public function applicable_formats() {
        // Only add at user-profile and other course-profiles (weeks & topics).
        return array('all' => true);
    }

    /**
     * get_content function
     *
     */
    public function get_content() {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            return '';
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $myuserid = $USER->id;
        $hisuserid = optional_param('id', 0, PARAM_INT);
        $courseid = optional_param('course', 0, PARAM_INT);

        if (((strpos($this->page->pagetype, 'user-profile') === 0) ||
                ((strpos($this->page->pagetype, 'course-view-topics') === 0) && ($courseid != 0)) ||
                ((strpos($this->page->pagetype, 'course-view-weeks') === 0) && ($courseid != 0))) && $myuserid != $hisuserid) {
            $commoncourses = enrol_get_shared_courses($myuserid, $hisuserid);
            if ($commoncourses != null) {
                foreach ($commoncourses as $common) {
                    $coursevisibility = $common->visible;
                    if ($coursevisibility == 1) {
                        $coursename = format_string($common->fullname);
                        $course = $common->id;
                        if ($course != $courseid) {
                            $url = './view.php?id='.$hisuserid.'&course='.$common->id;
                            $this->content->items[] = '<a href="'.$url.'">'.$coursename.'</a>';
                            $this->content->icons[] = '';
                        } else {
                            $this->content->items[] = $coursename;
                            $this->content->items[] = '';
                        }
                    }
                }
            }
        } else {
            $this->content = '';
        }
        return $this->content;
    }
}
