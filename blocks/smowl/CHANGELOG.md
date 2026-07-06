# Changelog

All notable changes to this project will be documented in this file.

## [Version 2026052700]

### Fixed

- Escape alert text in access control to prevent XSS in dynamic HTML insertion.
- Use the course module id when deleting SMOWL LTI activities during block removal and plugin uninstall.
- Generate SMOWL REST web service tokens with cryptographically secure random bytes.

### Improved

- Exclude development files, source maps, and bundled library metadata from release packages.

## [Version 2026031300]

### Fixed

- Escape alert text in access control to prevent XSS in dynamic HTML insertion.

## [Version 2025102300]

### Improved

- Removing some capabilites that are not needed
- Adding capabilities to get some types of activities (workshop and assignment)

## [Version 2025081900]

### Fixed

- Moodle 5 requires 2 extra capabilities fot the webservice user. Added: webservice/rest:use and moodle/site:viewparticipants

## [Version 2025061000]

### Improved

- Improve start quiz button access control (disabling and preventing click)

## [Version 2025060412]

### Improved

- Improved student role detection to prevent situations when the block is not showing up due to missing capabilities.
- Update query selector to detect start quiz button

## [Version 2025052702]

### Improved

- Introduced a dedicated role for LTI connection. This role has the required capabilities, eliminating the need for admin rights.

## [Version 2025030600]

### Fixed

- Avoid error when JWT class is declared or used by other block o part of moodle (now SmowlJWT).

## [Version 2025022400]

### Improved

- Updating LMS config after block update

## [Version 2025022400]

### Fixed

- Updating LMS config after block update

## [Version 2025020300]

### Added

- JWT token validation for the corner iframe


## [Version 2024120300]

### Removed

- file_put_contents function that was writing a log.txt file for debugging purposes

## [Version 2024112600]

### Removed

- No check accesrulesmowlcheckcam flag to show corner as student for any role

## [Version 2024102800]

### Changed

- Access control JS less invasive

## [Version 2024102100]

### Fixed

- Handle error when course deletion function doesn't found the block context.

### Improved

- Build script.

## [Version 2024092502]

### Added

- Zulu language support

## [Version 2024092501]

### Removed

Exam expel flag configuration feature

## [Version 2024092500]

### Fixed

- Text selection while dragging

### Improved

- Dragging performance

### Changed

- Text & color of notification according to Monitoring Block in Floating Mode

### Added

- Catalan language support
- Afrikaans language support
- Title attribute to the corner iframe
- Drag button tooltip

## [Version 2024092300]

### Removed

- Removed corner teacher demo view

## [Version 2024082000]

### Fixed

- Fixed loading controller twice in floating block

## [Version 2024081900]

### Fixed

- Fixed php error in floating block due to passing array to function that expects string

## [Version 2024072600]

### Fixed

- Dragging mode responsive problems, improved design

## [Version 2024071500]

### Added

- Added allow-popups-to-escape-sandbox to sandbox properties for iframe

## [Version 2024052700]

### Reverted

- Reverted use of some libs that only available from Moodle 4.2

## [Version 2024052000]

### Removed

- Css unused lines - OLMS uncompatible
- Package renaming all files

## [Version 2024041800]

### Fixed

- CA, GL AND EU nows shows ES translations instead of EN
- Fixed "Z-INDEX PROBLEM" with the draggable corner feature

### Added

- Change log file
- InteractJS library for draggable corner feature

### Changed

- Block author

## [Version 2024031400]

- Last Version witout changelog
