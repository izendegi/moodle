# moodle-tiny_fontsize

[![Moodle Plugin CI](https://github.com/finspire-fi/moodle-tiny_fontsize/actions/workflows/ci.yml/badge.svg)](https://github.com/finspire-fi/moodle-tiny_fontsize/actions/workflows/ci.yml)

A [TinyMCE](https://www.tiny.cloud/) editor plugin for Moodle that adds a font size picker to the toolbar and Format menu, letting users change the font size of selected text.

## Features

- Toolbar button and Format menu entry for choosing a font size
- The list of available sizes and the CSS unit they're expressed in (`pt`, `px`, `em`, `rem`, or `%`) are both configurable by an administrator
- If fewer than two sizes are configured, the picker is hidden rather than shown empty

## Requirements

- Moodle 4.1 (2022112800) or later

## Installation

Copy (or clone) this repository into your Moodle installation at:

```
lib/editor/tiny/plugins/fontsize
```

Then visit *Site administration &raquo; Notifications* to complete the installation.

## Settings

Go to *Site administration &raquo; Plugins &raquo; Text editor &raquo; TinyMCE editor &raquo; Font size* to configure:

| Setting | Description |
| --- | --- |
| Font sizes | The list of sizes offered in the picker, one per line |
| Font size unit | The CSS unit applied to every size (`pt`, `px`, `em`, `rem`, `%`) |

## License

Licensed under the [GNU GPL v3 or later](https://www.gnu.org/copyleft/gpl.html).
