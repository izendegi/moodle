<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException;
use Facebook\WebDriver\Exception\WebDriverException;
/**
 * Behat quizaccess_sebversion related steps and selector definitions.
 *
 * @package    quizaccess_sebversion
 * @category   test
 * @copyright  2025, Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_quizaccess_sebversion extends behat_base {
    use behat_session_trait;

    /**
     * Return the list of exact named selectors.
     *
     * @return array
     */
    public static function get_exact_named_selectors(): array {
        return [
            new behat_component_named_selector(
                'modal overlay',
                ["//div[(contains(@class, 'quizaccess_sebversion_overlay'))]"],
            ),
        ];
    }

    /**
     * Create a global SafeExamBrowser object to simulate an SEB instance. Also, save the version
     * string to the local storage where the client's Javascript can then recreate the SafeExamBrowser
     * object once the next page is loaded. When using the special string "no SEB" as the version, the
     * Javascript will know that it should not create the global object and thus not simulate SEB.
     *
     * @Given /^I simulate Safe Exam Browser version "(?P<version>[A-Za-z0-9._ ]+)" for the sebversion quizaccess plugin$/
     *
     * @param string $version simulated version string, or "no SEB"
     */
    public function i_simulate_safe_exam_browser_version_for_quizaccess_sebversion_plugin(string $version): void {
        $this->execute_script(
            "localStorage.setItem('quizaccess_sebversion_versionString', '$version')"
        );
        $this->execute_script(
            "window.SafeExamBrowser = { 'version': '{$version}' }"
        );
    }

    /**
     * Verify that a certain element on the page cannot be clicked, because it is covered by our
     * plugin's modal overlay.
     *
     * @Then /^I should not be able to click on "(?P<selector>[^"]+)" because of the sebversion quizaccess overlay$/
     *
     * @param string $selector element that should not be clickable
     */
    public function i_should_not_be_able_to_click_because_of_quizaccess_sebversion_plugin(string $selector) {
        $session = $this->getSession();
        $page = $session->getPage();
        $element = $page->find('css', $selector);

        // If the element cannot be found, throw the appropriate error.
        if (!$element) {
            throw new ElementNotFoundException($session, 'css', $selector);
        }

        // Try clicking the given element. This is expected to fail, and we want to check the
        // exception to make sure that the click would have been absorbed by our own modal. If
        // clicking fails for another reason, that would probably be a bug in our plugin.
        try {
            $element->click();
        } catch (Exception $e) {
            $expectederror = 'Other element would receive the click: <div class="quizaccess_sebversion_overlay"';
            if (strpos($e->getMessage(), $expectederror) !== false) {
                // Our plugin successfully blocked the click, return peacefully.
                return;
            }
            // The click failed for some other reason, re-throw the exception.
            throw $e;
        }

        // If there was no exception, that's a bad sign...
        throw new WebDriverException(
            "Clicking the {$selector} element succeeded, but it should have been blocked by a modal overlay."
        );
    }
}
