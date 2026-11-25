# Change log for the Edit roles by capability tool


## Changes in 5.0

* Update GitHub Actions CI workflow for Moodle 5.0.
* Fixed pipeline warnings.
* Rewrote Javascript in tool_editrolesbycap to remove YUI dependencies.
* Updated templates and sessionStorage usage to Javascript.
  Replaced use if a random cookie for storing filterValue with window.sessionStorage.
  This avoids the need to document/manage an extra cookie and follows modern browser best practices.


## Changes in 2.0

* Re-build the JavaScript with the latest version grunt.


## Changes in 1.9

* Fixed a bug which caused not all capabilities to appear in the list.


## Changes in 1.8

* Small styling improvements.


## Changes in 1.7

* Fix Behat to work in Moodle 3.6.


## Changes in 1.6

* Privacy API implementation.
* Fix some coding style.
* Due to privacy API support, this version now only works in Moodle 3.4+
  For older Moodles, you will need to use a previous version of this plugin.


## 1.5 and before

Changes were not documented here.
