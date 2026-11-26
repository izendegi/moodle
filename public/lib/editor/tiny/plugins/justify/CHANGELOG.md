# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## 1.1.0 - 2026-11-25

### Added
- Capability `tiny/justify:use` (given to every role by default), checked by the plugin on Moodle 4.5 and 5.x. Moodle 5.x expects it from TinyMCE plugins.
- PHPUnit test of the capability check.

### Changed
- `$plugin->supported = [405, 503]`: Moodle 4.5 and 5.3 (LTS).
- Continuous integration moved to the standard `ci.yml` (Moodle 4.5 and 5.3, PostgreSQL and MariaDB, code checker and PHPDoc without warnings, Behat).
- The PHPUnit test file is `tests/plugininfo_test.php`, with a final test class.
- Short bilingual README pointing to the documentation.
- `amd/build` rebuilt with Moodle's Grunt, now with the source maps (`.map`).

### Fixed
- ESLint warning (camelcase) in `amd/src/configuration.js`.
- Behat scenarios open the toolbar overflow ("More...") before clicking the Justify button, which a narrow form column hides there; they pass on Moodle 5.3.

## 1.0.22 - 2026-11-19

### Added
- Privacy API provider (`classes/privacy/provider.php`, null provider: the plugin stores no personal data).
- Bilingual documentation (English and Brazilian Portuguese) built with Sphinx and published on GitHub Pages.

### Changed
- GPL boilerplate and license links use `https://`.
- The test class in `tests/subplugininfo.php` is namespaced; the JavaScript test uses `@module`.
- The README states the actual requirement, Moodle 4.5.
- Repository references point to `moodle-by-kelsoncm/moodle-tiny_justify`.

### Removed
- The `lang/pt_br` folder: translations come through AMOS (lang.moodle.org).
- The empty `db/install.php` and `db/upgrade.php`.

## 1.0.20 - 2026-03-03

### Added
- Initial release of the `tiny_justify` plugin for Moodle's TinyMCE editor
- Justify button added to the TinyMCE toolbar
- Full text justification support for Moodle content areas
