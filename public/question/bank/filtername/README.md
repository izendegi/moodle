# Question bank filter name plugin

Filter questions in question bank using question name and text.

Plugin adds new filter to question bank which allows to search questions with given phrase. Plugin supports searching multiple phrases in question name and optionally also in question text. Plugin is integrated with all three condition types (join types): *All*, *Any* and *None*.

This plugin is designed for Moodle 4.3, 4.4 and newer versions.

## Motivation for this plugin

In moodle 4.3, there has been a [redesign of the filtering/searching UI](https://tracker.moodle.org/browse/MDL-72321) in the question bank. Now the filters in the question bank are based on the core filter API, which is also used on the course participant page. As a result of this redesign, the [*local_searchquestions*](https://moodle.org/plugins/local_searchquestions) plugin that allowed searching questions by name is not compatible with moodle 4.3 or higher. The *qbank_filtername* is therefore a kind of replacement for the *local_searchquestions* plugin. The *qbank_filtername* plugin is integrated with the new filter UI in the question bank and extends the question search functionality taking advantage of the core API for filters.

## Installation

Install the plugin like any other qbank plugin to folder **/question/bank/filtername**

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins.

## Maintainers

This plugin is developed and maintained by:

E-Learning Center of the Lodz University of Technology

## Copyright

The copyright of this plugin is held by:

E-Learning Center of the Lodz University of Technology

Individual copyrights of individual developers are tracked in PHPDoc comments and Git commits.
