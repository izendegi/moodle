FontAwesome Icon Picker
=======================

Source
------

https://github.com/farbelous/fontawesome-iconpicker

Installation
------------

- Copy fontawesome-iconpicker.js to amd/src/fontawesome-iconpicker.js
- Split `fontawesome-iconpicker.js` in two files
  - The jQuery plugin position.js to `jquery-position.js`
  - The icon picker to `fontawesome-iconpicker.js`
- Add `/* eslint-disable */` at the top of both files file
- Edit `fontawesome-iconpicker.js` to include dependency on `jquery-position`
  - Line 5: `define([ "jquery", "mod_structlabel/jquery-position" ], a);`
- Use Moodle's `grunt` process to make the build files

Building notes
--------------

The version of Uglify used in Moodle 3.6 does not compile the `fontawesome-iconpicker` properly. You must update the package `grunt-contrib-uglify` with `npm install --save-dev grunt-contrib-uglify` prior to building the JavaScript files. You may run into dependency issues, track them down, resolve them, build the files and revert the changes to Moodle core.
