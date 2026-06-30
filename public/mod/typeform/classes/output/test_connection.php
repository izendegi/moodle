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
 * Test connection output class
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_typeform\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Test connection output class
 *
 * @package    mod_typeform
 * @copyright  2026 3ipunt {@link https://www.tresipunt.com}
 * @author     3IPUNT <contacte@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_connection implements renderable, templatable {
    /** @var string API token (masked) */
    protected $apitoken;

    /** @var string Allowed workspaces display */
    protected $allowedworkspaces;

    /** @var array Test results */
    protected $testresults;

    /** @var array Sample forms */
    protected $sampleforms;

    /** @var int Total form count */
    protected $formcount;

    /** @var bool Whether API token is configured */
    protected $hastoken;

    /** @var string Return URL */
    protected $returnurl;

    /**
     * Constructor
     *
     * @param string $apitoken Masked API token
     * @param string $allowedworkspaces Allowed workspaces display
     * @param array $testresults Test results
     * @param array $sampleforms Sample forms (first 5)
     * @param int $formcount Total form count
     * @param bool $hastoken Whether API token is configured
     * @param string $returnurl Return URL
     */
    public function __construct($apitoken, $allowedworkspaces, $testresults, $sampleforms, $formcount, $hastoken, $returnurl) {
        $this->apitoken = $apitoken;
        $this->allowedworkspaces = $allowedworkspaces;
        $this->testresults = $testresults;
        $this->sampleforms = $sampleforms;
        $this->formcount = $formcount;
        $this->hastoken = $hastoken;
        $this->returnurl = $returnurl;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;

        $data = new \stdClass();
        $data->hastoken = $this->hastoken;
        $data->apitoken = $this->apitoken;
        $data->allowedworkspaces = $this->allowedworkspaces;
        $data->returnurl = $this->returnurl;

        // Process test results with notifications
        $processedresults = [];
        $testnumber = 1;
        foreach ($this->testresults as $result) {
            $processed = new \stdClass();
            $processed->testnumber = $testnumber++;
            $processed->test = $result['test'];
            $processed->result = $result['result'];

            // Create notification data
            $notification = new \stdClass();
            $notification->message = $result['message'] ?? '';
            $notification->announce = true;
            $notification->closebutton = false;

            if ($result['result']) {
                $notification->issuccess = true;
            } else if (isset($result['warning']) && $result['warning']) {
                $notification->iswarning = true;
            } else {
                $notification->iserror = true;
            }

            $processed->notification = $notification;

            // Add forms data if available
            if (isset($result['count'])) {
                $processed->hasforms = ($result['count'] > 0);
                $processed->formcount = $result['count'];
                if ($result['count'] > 0 && !empty($this->sampleforms)) {
                    $processed->sampleforms = $this->sampleforms;
                    $processed->hasmoreforms = ($result['count'] > count($this->sampleforms));
                    if ($processed->hasmoreforms) {
                        $processed->morecount = $result['count'] - count($this->sampleforms);
                    }
                }
            }

            $processedresults[] = $processed;
        }
        $data->testresults = $processedresults;

        // Calculate summary
        $passed = 0;
        $failed = 0;
        foreach ($this->testresults as $result) {
            if ($result['result']) {
                $passed++;
            } else {
                $failed++;
            }
        }
        $data->summary = new \stdClass();
        $data->summary->passed = $passed;
        $data->summary->failed = $failed;
        $data->summary->allpassed = ($failed == 0);

        // Summary notification
        $summarynotification = new \stdClass();
        $summarynotification->announce = true;
        $summarynotification->closebutton = false;
        if ($data->summary->allpassed) {
            $summarynotification->issuccess = true;
            $summarynotification->message = get_string('alltestspassed', 'typeform', $passed);
        } else {
            $summarynotification->iserror = true;
            $summarynotification->message = get_string(
                'sometestsfailed',
                'typeform',
                (object)['passed' => $passed, 'failed' => $failed]
            );
        }
        $data->summary->notification = $summarynotification;

        // No token notification
        $data->notokennotification = new \stdClass();
        $data->notokennotification->message = get_string('notokenconfigured', 'typeform');
        $data->notokennotification->iserror = true;
        $data->notokennotification->announce = true;
        $data->notokennotification->closebutton = false;

        return $data;
    }
}
