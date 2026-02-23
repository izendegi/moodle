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

/**
 * Script to show a modal overlay in case of outdated versions of the
 * Safe Exam Browser.
 *
 * @module     quizaccess_sebversion/overlay
 * @copyright  2025, Philipp Imhof
 * @author     Philipp Imhof
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as String from 'core/str';

/**
 * Default messages, in case the strings cannot be fetched from the language file.
 */
const DEFAULT_MESSAGES = {
    'update': 'Please update your Safe Exam Browser in order to attempt this quiz. You need at least version ',
    'invalid': 'The version of your Safe Exam Browser could not be determined. '
        + 'Please download the most recent official version and try again',
};

/**
 * Setup the environment, check the version and react accordingly.
 *
 * @param {string} minVersionWin the minimum version of SEB on Windows, e. g. 3.10.0
 * @param {string} minVersionMac the minimum version of SEB on macOS (Mac/iPad), e. g. 3.6.0
 * @param {boolean} behat wheter we are currently in a Behat acceptance test.
 */
export const init = async(minVersionWin, minVersionMac, behat) => {
    testisVersionAtLeast();
    // Inside Safe Exam Browser, there is a global SafeExamBrowser object with, among others,
    // a version attribute. The object is not normally available in other browsers. As acceptance
    // tests use regular browsers, we will have to simulate being in Safe Exam Browser. The
    // test feature will have left the desired version string in the local storage. We create
    // the SafeExamBrowser global object and copy the version string from the local storage. If
    // the simulated version is the special string "no SEB", we do not create the SafeExamBrowser
    // object in order to simulate the fact that there is no SEB at all.
    if (behat) {
        const simulatedVersion = localStorage.getItem('quizaccess_sebversion_versionString');
        if (simulatedVersion !== 'no SEB') {
            window.SafeExamBrowser = {
                'version': simulatedVersion,
            };
        }

        // If we are in a Behat test, we will also run "unit tests" for the version checker.
        testisVersionAtLeast();
    }

    // Fetch the version string from the SafeExamBrowser object, as described above.
    const versionString = window.SafeExamBrowser?.version ?? '';

    // By default, we assume that the overlay will be needed.
    let overlayNeeded = true;
    let targetVersion = '';

    // The version string has the following format, depending on the platform:
    // - Safe Exam Browser_macOS_3.5_15487_org.safeexambrowser.SafeExamBrowser
    // - Safe Exam Browser_iOS_3.5_15487_org.safeexambrowser.SafeExamBrowser
    // - SEB_Windows_3.9.0.787
    // The formats can be found in the SafeExamBrowser GitHub repositories:
    // -> seb-mac/Classes/BrowserComponents/SEBBrowserController.m --> look for appVersion
    // -> seb-mac/SEB/SEBViewController.m --> look for appVersion (iOS)
    // -> seb-win-refactoring/SafeExamBrowser.Browser/Content/Api.js
    // If the string does not match either of these formats, something could be
    // wrong, e. g. it could be a custom build.
    if (versionString.includes('macOS') || versionString.includes('iOS')) {
        overlayNeeded = !checkMacVersion(versionString, minVersionMac);
        targetVersion = minVersionMac;
    } else if (versionString.includes('Windows')) {
        overlayNeeded = !checkWinVersion(versionString, minVersionWin);
        targetVersion = minVersionWin;
    } else {
        overlayNeeded = true;
    }

    if (overlayNeeded) {
        await addOverlay(targetVersion);
    }
};

/**
 * Take the version string, as declared by a Mac build of SEB, and extract the version number for
 * comparision with the the requested minimum version. Comparison is done by a helper function.
 *
 * @param {String} versionString the version string as declared by the browser
 * @param {String} minVersion the requested minimum version of SEB
 * @returns {boolean}
 */
const checkMacVersion = (versionString, minVersion) => {
    // The version string on Mac must have exactly five parts. The third part will be the
    // version number, e. g. Safe Exam Browser_macOS_3.5_15487_org.safeexambrowser.SafeExamBrowser
    // or Safe Exam Browser_iOS_3.5_15487_org.safeexambrowser.SafeExamBrowser
    const parts = versionString.split('_');
    if (parts.length !== 5) {
        return false;
    }

    return isVersionAtLeast(parts[2], minVersion);
};

/**
 * Take the version string, as declared by a Windows build of SEB, and extract the version number for
 * comparision with the the requested minimum version. Comparison is done by a helper function.
 *
 * @param {String} versionString the version string as declared by the browser
 * @param {String} minVersion the requested minimum version of SEB
 * @returns {boolean}
 */
const checkWinVersion = (versionString, minVersion) => {
    // The version string on Windows must have exactly three parts. The third part will
    // be the version number, e. g. SEB_Windows_3.9.0.787.
    const parts = versionString.split('_');
    if (parts.length !== 3) {
        return false;
    }

    return isVersionAtLeast(parts[2], minVersion);
};

/**
 * Compare two version strings (major.minor.patchlevel) and check whether the given
 * version is at least as recent as the reference version.
 *
 * @param {String} givenVersion given version to be compared
 * @param {String} minVersion minimum version to be compared with
 * @returns {boolean}
 */
const isVersionAtLeast = (givenVersion, minVersion) => {
    const givenParts = givenVersion.split('.');
    const expectedParts = minVersion.split('.');

    // First, we compare the major version. If it is higher or lower,
    // we do not need to check other parts.
    if (parseInt(givenParts[0]) > parseInt(expectedParts[0])) {
        return true;
    }
    if (parseInt(givenParts[0]) < parseInt(expectedParts[0])) {
        return false;
    }

    // Then, we compare the minor version. Again, if it is higher or lower, we
    // do not need to check the patchlevel.
    if (parseInt(givenParts[1]) > parseInt(expectedParts[1])) {
        return true;
    }
    if (parseInt(givenParts[1]) < parseInt(expectedParts[1])) {
        return false;
    }

    // Finally, if there is a patchlevel, we compare it as well. On Mac, SEB might omit the
    // .0 part, e. g. use '3.6' instead of '3.6.0'.
    if (parseInt(givenParts[2] ?? '0') < parseInt(expectedParts[2] ?? '0')) {
        return false;
    }

    return true;
};

/**
 * Add a modal overlay on top of the current page, actively blocking students from interacting
 * with the quiz' input fields and showing an information text.
 *
 * @param {String} targetVersion the target version the student would need to attempt the quiz,
 *                               empty if we could not determine the SEB variant (Mac / Windows)
 */
const addOverlay = async(targetVersion) => {
    // We have two possible messages, one (the 'update' variant) telling the student they have to
    // update their SEB to at least a given version and one (the 'invalid') variant telling them
    // that the version could not be determined. This allows them to check the problem with their
    // school, in case the version strings suddenly change our script is not able to correctly
    // recognize them anymore.
    const variant = (targetVersion === '' ? 'invalid' : 'update');

    // Fetch the correct variant of the message, use our default texts as the fallback
    // in case the text cannot be fetched.
    let message = '';
    try {
        message = await String.get_string(
            `overlay_text_${variant}`,
            'quizaccess_sebversion',
            {'version': targetVersion}
        );
    } catch (err) {
        window.console.error(err);
        message = DEFAULT_MESSAGES[variant] + targetVersion + '.';
    }

    // Create our modal overlay.
    const overlay = document.createElement('div');
    overlay.classList.add('quizaccess_sebversion_overlay');
    overlay.setAttribute('role', 'alertdialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.tabIndex = 0;
    overlay.textContent = message;

    // Add our modal overlay to the DOM and make it the focused element.
    document.querySelector('body').appendChild(overlay);
    overlay.focus();

    // Make sure that elements outside of the overlay cannot gain focus.
    document.addEventListener('focus', (e) => {
        if (!overlay.contains(e.target)) {
            e.stopPropagation();
            e.preventDefault();
            overlay.focus();
        }
    }, true);

    // Block all keyboard and pointer events from reaching the page.
    const events = [
        'keydown', 'keyup', 'keypress', 'input',
        'mousedown', 'mouseup', 'click', 'dblclick',
        'contextmenu',
        'pointerdown', 'pointerup',
        'touchstart', 'touchend',
    ];
    const blocker = (e) => {
        e.stopPropagation();
        e.preventDefault();
    };
    for (const e of events) {
        document.addEventListener(e, blocker, true);
    }
};

/**
 * This is a "unit test" for the isVersionAtLeast() function. It will run some test cases and
 * output a status message to the page. The message will then be caught by the behat test.
 */
const testisVersionAtLeast = () => {
    // Test cases to be checked.
    const cases = [
        [true, ['1.2.3', '1.2.3']],
        [true, ['2.0.0', '1.9.9']],
        [true, ['2.0', '2.0.0']],
        [true, ['2.0', '2.0']],
        [true, ['2.0', '1.9.9']],
        [false, ['1.0.0', '2.0.0']],
        [true, ['1.5.0', '1.4.9']],
        [false, ['1.3.0', '1.4.0']],
        [true, ['1.2.5', '1.2.3']],
        [false, ['1.2.1', '1.2.3']],
        [true, ['1.2', '1.2.0']],
        [false, ['1.2', '1.2.1']],
        [true, ['1.2.1', '1.2']],
        [true, ['1.2.0', '1.2']],
        [true, ['10.12.3', '9.99.99']],
        [false, ['10.1.3', '10.10.0']],
    ];

    // For easy detection in the Behat test, we will output some text to the web page.
    const info = document.createElement('p');
    info.textContent = 'quizaccess_sebversion unit tests successful';
    for (let c of cases) {
        let result = isVersionAtLeast(c[1][0], c[1][1]);
        if (result !== c[0]) {
            info.textContent = 'quizaccess_sebversion unit test failed';
            window.console.error('failed unit test:', c);
        }
    }

    document.body.append(info);
};

export default {init};