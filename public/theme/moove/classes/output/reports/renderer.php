<?php

/**
 * Reports main renderer
 *
 * @package     theme_moove
 * @copyright   2021 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace theme_moove\output\reports;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use renderable;

/**
 * Moove reports renderable class.
 *
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */
class renderer extends plugin_renderer_base {
    /**
     * Defer the instance in index to template.
     *
     * @param \renderable $page
     *
     * @return bool|string
     *
     * @throws \moodle_exception
     */
    public function render_index(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('theme_moove/moove_reports/index', $data);
    }

    /**
     * Defer the instance in period to template.
     *
     * @param \renderable $page
     *
     * @return bool|string
     *
     * @throws \moodle_exception
     */
    public function render_period(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('theme_moove/moove_reports/period', $data);
    }

    /**
     * Defer the instance in online users to template.
     *
     * @param \renderable $page
     * @return bool|string
     * @throws \moodle_exception
     */
    public function render_onlineusers(renderable $page) {
        $data = $page->export_for_template($this);

        return parent::render_from_template('theme_moove/moove_reports/onlineusers', $data);
    }
}
