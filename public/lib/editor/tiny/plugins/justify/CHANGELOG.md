# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [1.0.22] - 2026-11-19

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

## [1.0.20] - 2026-03-03

### Added
- Initial release of the `tiny_justify` plugin for Moodle's TinyMCE editor
- Justify button added to the TinyMCE toolbar
- Full text justification support for Moodle content areas
