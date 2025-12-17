![GitHub Release](https://img.shields.io/github/v/release/PhilippImhof/moodle-quizaccess_sebversion)
[![Automated code checks](https://github.com/PhilippImhof/moodle-quizaccess_sebversion/actions/workflows/checks.yml/badge.svg)](https://github.com/PhilippImhof/moodle-quizaccess_sebversion/actions/workflows/checks.yml) [![Automated testing](https://github.com/PhilippImhof/moodle-quizaccess_sebversion/actions/workflows/testing.yml/badge.svg)](https://github.com/PhilippImhof/moodle-quizaccess_sebversion/actions/workflows/testing.yml)

moodle-quizaccess_sebversion
----------------------------

This is a quiz access rule plugin that allows teachers to enforce a minimum version of the Safe Exam Browser for their quiz. The required version can be set side-wide by the administrators.


#### Installation

Install the plugin to the folder `$MOODLE_ROOT/mod/quiz/accessrule/sebversion`.

For more information, please see the [Moodle docs](https://docs.moodle.org/en/Installing_plugins).


#### Usage

1. Create a quiz.
2. In the settings form, click on "Safe Exam Browser".
3. At the end of the section, set "Enforce minimum SEB version" to "Yes".

Note that the plugin will only run if the quiz requires the use of the Safe Exam Browser and if the user does not have permission to bypass the SEB. Hence, you will have to try it in a student role to see it in action.