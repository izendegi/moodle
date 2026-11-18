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

namespace enrol_database;

/**
 * Tests for the category lookup and autocreation of the external database enrolment plugin.
 *
 * @package    enrol_database
 * @copyright  2026 Mondragon Unibertsitatea
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversMethod(\enrol_database_plugin::class, 'get_categoryid')]
final class get_categoryid_test extends \advanced_testcase {

    /**
     * Returns the plugin configured with the given separator and autocreation setting.
     *
     * @param string $separator category separator, empty for none.
     * @param bool $autocreate whether missing categories are created.
     * @return \enrol_database_plugin
     */
    private function get_plugin(string $separator, bool $autocreate): \enrol_database_plugin {
        $plugin = enrol_get_plugin('database');
        $plugin->set_config('categoryseparator', $separator);
        $plugin->set_config('autocreatecategory', (int) $autocreate);
        return $plugin;
    }

    /**
     * Without separator nor autocreation, only an existing category is returned.
     */
    public function test_no_separator_no_autocreate(): void {
        $this->resetAfterTest();
        $cat = $this->getDataGenerator()->create_category(['idnumber' => 'cat1']);
        $plugin = $this->get_plugin('', false);
        $trace = new \null_progress_trace();

        $this->assertEquals($cat->id, $plugin->get_categoryid('cat1', $trace));
        $this->assertFalse($plugin->get_categoryid('missing', $trace));
    }

    /**
     * Without separator and with autocreation, an existing category is reused and a missing one is created.
     */
    public function test_no_separator_autocreate(): void {
        global $DB;
        $this->resetAfterTest();
        $cat = $this->getDataGenerator()->create_category(['idnumber' => 'cat1']);
        $plugin = $this->get_plugin('', true);
        $trace = new \null_progress_trace();
        $count = $DB->count_records('course_categories');

        $this->assertEquals($cat->id, $plugin->get_categoryid('cat1', $trace));
        $this->assertEquals($count, $DB->count_records('course_categories'));

        $newid = $plugin->get_categoryid('cat2', $trace);
        $this->assertEquals($newid, $DB->get_field('course_categories', 'id', ['idnumber' => 'cat2']));
        $this->assertEquals($count + 1, $DB->count_records('course_categories'));
    }

    /**
     * With separator and without autocreation, only the category matching the whole path is returned.
     */
    public function test_separator_no_autocreate(): void {
        $this->resetAfterTest();
        $parent = $this->getDataGenerator()->create_category(['idnumber' => 'a']);
        $child = $this->getDataGenerator()->create_category(['idnumber' => 'a/b', 'parent' => $parent->id]);
        $plugin = $this->get_plugin('/', false);
        $trace = new \null_progress_trace();

        $this->assertEquals($child->id, $plugin->get_categoryid('a/b', $trace));
        $this->assertEquals($child->id, $plugin->get_categoryid(' a / b ', $trace));
        $this->assertEquals($parent->id, $plugin->get_categoryid('a', $trace));
        // Falling back to the parent category would put the course in the wrong place.
        $this->assertFalse($plugin->get_categoryid('a/c', $trace));
        $this->assertFalse($plugin->get_categoryid('a/b/c', $trace));
        $this->assertFalse($plugin->get_categoryid('/a', $trace));
    }

    /**
     * With separator and autocreation, the missing part of the tree is created and existing categories are reused.
     */
    public function test_separator_autocreate(): void {
        global $DB;
        $this->resetAfterTest();
        $parent = $this->getDataGenerator()->create_category(['idnumber' => 'a']);
        $plugin = $this->get_plugin('/', true);
        $trace = new \null_progress_trace();
        $count = $DB->count_records('course_categories');

        $id = $plugin->get_categoryid('a/b/c', $trace);
        $this->assertEquals($count + 2, $DB->count_records('course_categories'));
        $b = $DB->get_record('course_categories', ['idnumber' => 'a/b'], '*', MUST_EXIST);
        $c = $DB->get_record('course_categories', ['idnumber' => 'a/b/c'], '*', MUST_EXIST);
        $this->assertEquals($c->id, $id);
        $this->assertEquals($parent->id, $b->parent);
        $this->assertEquals($b->id, $c->parent);
        $this->assertEquals('c', $c->name);

        // A second call reuses the tree.
        $this->assertEquals($c->id, $plugin->get_categoryid('a/b/c', $trace));
        $this->assertEquals($count + 2, $DB->count_records('course_categories'));
    }

    /**
     * The idnumber of the created categories is their path with the configured separator.
     */
    public function test_other_separator(): void {
        global $DB;
        $this->resetAfterTest();
        $plugin = $this->get_plugin('|', true);
        $trace = new \null_progress_trace();

        $id = $plugin->get_categoryid('a | b|c', $trace);
        $c = $DB->get_record('course_categories', ['idnumber' => 'a|b|c'], '*', MUST_EXIST);
        $this->assertEquals($c->id, $id);
        $this->assertEquals('c', $c->name);
        $this->assertTrue($DB->record_exists('course_categories', ['idnumber' => 'a|b', 'id' => $c->parent]));
        $this->assertFalse($DB->record_exists('course_categories', ['idnumber' => 'a/b/c']));

        $plugin->set_config('autocreatecategory', 0);
        $this->assertEquals($c->id, $plugin->get_categoryid('a|b|c', $trace));
        $this->assertFalse($plugin->get_categoryid('a/b/c', $trace));
    }
}
