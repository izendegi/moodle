<?php
namespace filter_gitquotes\hooks;

defined('MOODLE_INTERNAL') || die();

use core\hook\output\before_standard_head_html_generation as base_hook;

/**
 * Hook subscriber for adding extra HTML head content.
 */
class before_standard_head_html_generation {
    /**
     * Execute when the HTML head is being generated.
     *
     * @param base_hook $hook
     * @return void
     */
    public static function callback(base_hook $hook): void {
        global $PAGE;

        // Add your CSS/JS or other resources here.
        $PAGE->requires->css('/filter/gitquotes/styles.css');

        // If you added JS, example:
        // $PAGE->requires->js('/filter/gitquotes/script.js');
    }
}