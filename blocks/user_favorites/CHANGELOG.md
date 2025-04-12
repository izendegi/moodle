# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)

# Plugin version.php information

```php
// Example

// Plugin release number corresponds to the lasest tested Moodle version in which the plugin has been tested.
$plugin->release = '3.5.7'; // [3.5.7]

// Plugin version number corresponds to the latest plugin version.
$plugin->version = 2023030200; // 2023-03-02
```

# How do I make a good changelog?

Guiding Principles

* Changelogs are for humans, not machines.
* There should be an entry for every single version.
* The same types of changes should be grouped.
* The latest version comes first.
* The release date of each version is displayed.

Types of changes

* **Added** for new features.
* **Changed** for changes in existing functionality.
* **Deprecated** for soon-to-be removed features.
* **Removed** for now removed features.
* **Fixed** for any bug fixes.
* **Security** in case of vulnerabilities.

## Version (4.4.0) - 2024-04-05
- Tested for Moodle (LMS) 4.4 and PHP 8.1

## Version (4.3.0) - 2024-02-16
- Tested and refactored for Moodle (LMS) 4.3

## Version (4.2.0) - 2023-12-20
- Branche 4.2 for just 4.2 use
- Validation M4.2

## Version (4.1.3) - 2023-12-20

##### Update
- Fix incorrect security risk flags on the capabilites.
- Fix issue moving favorites #23

## Version (4.1.2) - 2023-05-09

##### Update
- Fix Block drawer breaks in Moodle 4.1 #16 thanks to @stopfstedt for the contribution (https://github.com/Lesterhuis-Training-en-Consultancy/moodle-block-user_favorites/releases/tag/v4.1.2)

## Version (4.1.1) - 2023-03-02

##### Update
- Move externallib.php to namespaced external API.

##### Added
- Functionality to sort the user favorites usin AJAX requests
- Update version number to 4.1.1 no issues found
- Testen on PHP 8 - no issues found
- Allow user to mark a page with # as favorite.

## Version (3.10.1) - 2020-11-14

##### Added
- Updated version number, no issues found.

##### Changed

- Updated version number, no issues found.

##### Removed

- Remove `.eslintrc` `Gruntfile.js` and `packages.json` from the project causes Travis issues.

## Version (3.9.1) - 2020-07-12

##### Fix

- GH-8 External API nested Optional url (Thanks @ewallah)

## Version (3.9) - 2020-05-06

##### Changed

- Updated version number, no issues found.
- Minimum version PHP 7.2

## Version (3.8) - 2019-10-30

##### Changed

- Updated version number, no issues found.

## Version (3.7.2) - 2019-09-17

##### Fixed

- [ISSUE #3](https://github.com/MFreakNL/moodle-block-user_favorites/issues/3) Saving user favourites to a separate
  table.
- Upgrade script `user_preference` -> `block_user_favorites` using a separate table.
- Implement privacy provider for the new table.

## Version (3.5.3) - 2019-05-20

##### Added

- Release of the first official stable version.
