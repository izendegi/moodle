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
 * This file contains the SMOWL output renderers class.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . "/blocks/smowl/lib/php-jwt/src/JWT.php");
use Smowl\Firebase\JWT\SmowlJWT;

/**
 * SMOWL output renderers class.
 */
class block_smowl_renderer extends plugin_renderer_base {

    /**
     * Return the manage page header.
     * @see header_logo_smowl()
     * @see html_writer::tag()
     * @param array $row tab elements
     * @param string $view selected tab
     * @return string
     */
    public function header_manage($row, $view) {
        $return = $this->output->header();
        // Image SMOWL header.
        // By LTI Change.
        if ($row) {
            $return .= $this->header_logo_smowl();
            $return .= html_writer::tag('h2', get_string($view, 'block_smowl'), ['class' => 'smowltitle']);
            $return .= $this->output->tabtree($row, $view);
        }

        return $return;
    }

    /**
     * Header logo
     * @see html_writer::img()
     * @return string
     */
    public function header_logo_smowl() {
        return html_writer::img($this->output->image_url('smowl', 'block_smowl'),
            get_string('pluginname', 'block_smowl'), ['class' => 'headersmowl']);
    }

    /**
     * Create SMOWL iframe student call
     * @param int $showdemocam to call the demo webcam file.
     * @see $USER
     * @see $COURSE
     * @see $CFG
     * @see current_language()
     * @see html_writer::tag()
     * @return string
     */
    public function get_iframe_student($floating_block = false) {
        global $USER, $COURSE, $CFG;

        // If I am not in a task return void.
        if (!isset($this->page->cm->modname)) {
             return '';
        }

        // Entity setting setted.
        if (!block_smowl_check_entity()) {
            return $this->output->notification(get_string('noticeemptysmowlconfig', 'block_smowl'), 'danger');
        }

        $entity = get_config('block_smowl', 'entity');
        $password = get_config('block_smowl', 'password');
        $courselink = urlencode($this->page->url);

        $params = [
            'entityName' => $entity,
            'entityKey' => $password,
            'activityType' => $this->page->cm->modname,
            'activityModule' => $this->page->cm->id,
            'activityContainerId' => $COURSE->id,
            'userId' => $USER->id,
            'lang' => current_language(),
            'type' => '3',
            'userName' => fullname($USER),
            'userEmail' => $USER->email,
        ];
        $idactivity = $this->page->cm->instance;
        $savephoto = '1';

        // I'm in a Quiz.
        if ($this->page->cm->modname == 'quiz') {
            $pagetypepatterns = matching_page_type_patterns($this->page->pagetype);
            // Inside attempt or summary (user can still go back to the attempt with "Return to attempt").
            if ($pagetypepatterns[0] == 'mod-quiz-attempt' ||
                    $pagetypepatterns[0] == 'mod-quiz-summary') {
                // Savephoto is still 1.
                // With attempts tracking we modify $idactivity.
                if (get_config('block_smowl', 'attempttracking')) {
                    $idattempt = required_param('attempt', PARAM_INT);
                    $idactivity .= '_'.$idattempt;
                }
            } else if ($pagetypepatterns[0] == 'mod-quiz-view' ||
                    $pagetypepatterns[0] == 'mod-quiz-review' ||
                    $pagetypepatterns[0] == 'mod-quiz-comment' ||
                    $pagetypepatterns[0] == 'mod-quiz-report' ||
                    $pagetypepatterns[0] == 'mod-quiz-edit') {
                // We only save photo in quiz attempt.
                $savephoto = '0';
            }
        }
        $params['activityId'] = $idactivity;
        $params['isMonitoring'] = $savephoto;
        $params['activityUrl'] = $courselink;

        $paramsToEncode = [
            'entityKey',
            'userId',
            'activityType',
            'activityId',
            'activityContainerId',
            'activityModule',
            'isMonitoring'
        ];

        $paramsForJWT = array_intersect_key($params, array_flip($paramsToEncode));
        $otherPostParams = array_diff_key($params, $paramsForJWT);
        $payload = [
            "iss" => "smowl_moodle_plugin",
            "aud" => parse_url($CFG->wwwroot, PHP_URL_HOST),
            "iat" => time(),
            "exp" => time() + 3600*12,
            "data" => $paramsForJWT
        ];
        $key = get_config('block_smowl', 'smowljwtsecret');
        $jwt = SmowlJWT::encode($payload, $key, 'HS256');
        $finalParams = array_merge(['token' => $jwt], $otherPostParams);

        require_once($CFG->dirroot . "/blocks/smowl/classes/smowl_connection.php");
        $link = block_smowl_connection::get_urlstudentview();
        $url = new moodle_url($link, $finalParams);

        $height = get_config('block_smowl', 'blockheight');

        $attributes = [
            'title' => 'smowl widget',
            'name' => 'smowliframe',
            'allow' => 'microphone; camera',
            'sandbox' => 'allow-top-navigation allow-scripts allow-modals allow-same-origin allow-popups allow-downloads allow-popups-to-escape-sandbox',
            'style' => '',
            'width' => '100%',
            'frameborder' => '0',
            'height' => $height,
            'src' => $url,
            'allowfullscreen' => '',
            'scrolling' => 'no',
        ];

        if ($floating_block) {
            $attributes['data-src'] = $url;
            unset($attributes['src']);
        }

        $iframe = html_writer::tag('iframe', '', $attributes);

        return $iframe;
    }

    /**
     * Create SMOWL Manage groups description.
     * @return string
     */
    public function view_managegroups_description() {
        $content = get_string('managegroupsformintro', 'block_smowl');
        $classes = 'form-group col-12';
        $attributes = ['id' => 'block_smowl_managegroupsdescription'];
        $html = html_writer::div($content, $classes, $attributes );
        return $html;
    }

    /**
     * Render the contents whith necesary information to open in popup.
     *
     * @param string $content the content of block.
     * @return string HTML
     */
    public function content_block_popup($content) {
        $move = '<div id="block_smowl_drag"><i class="icon fa fa-arrows" aria-hidden="true"></i><div class="tooltip">'.get_string('drag_me', 'block_smowl').'</div></div>';
        $classes = '';
        return html_writer::div($move.$content, $classes, ['id' => 'block_smowl_content']);
    }
    /**
     * Render the contents whith necesary information to open panel.
     *
     * @param string $title the title of block.
     * @param string $text the text of block.
     * @param string $textbutton button text.
     * @param string $url the link for the button.
     * @return string HTML
     */
    public function get_block_course_content($title, $text, $textbutton, $url) {

        $cardlink = html_writer::link($url, $textbutton, ['class' => 'btn-seconded']);
        $colbutton = html_writer::tag('div', $cardlink, ['class' => 'column c1']);
        $button = html_writer::tag('div', $colbutton, ['class' => 'card-link']);

        $subtitle = html_writer::tag('p', $text, ['class' => 'sub-title']);
        $cardtitle = html_writer::tag('h3', $title, ['class' => 'title']);
        $coltitle = html_writer::tag('div', $cardtitle . $subtitle, ['class' => 'col-title']);
        $title = html_writer::tag('div', $coltitle, ['class' => 'card-title2 d-inline']);

        $cardbody = html_writer::tag('div', $title . $button, ['class' => 'card-body']);
        $aside = html_writer::tag('aside', $cardbody);
        $boxblue = html_writer::tag('section', $aside, ['class' => 'boxBlue']);
        return $boxblue;
    }
}