Structured label
----------------

Moodle plugin for displaying structured content on a course page.

Dependencies
------------

- Moodle 3.6 onwards

Optional:

- Plugin mod_bootstrapelements: used to display the icon picker

Installation
------------

1. Place the content of this repository in `mod/structlabel`.
2. Visit 'Site administration > Notifications' to trigger the installation.

Configuration
-------------

Visit the settings page at _Site administration > Plugins > Activity modules > Structured label_ for all options.

Building JavaScript
-------------------

The FontAwesome IconPicker has issues with Moodle core uglifier, see notes in `lib/fontawesome-iconpicker` prior to overriding the existing build files.
