# Moodle Plugin: Tiny Orphanedfiles

A plugin for Moodle's TinyMCE editor to help clean up **orphaned files** that are no longer used in the editor content.

## Description

During the use of the Tiny Editor, you can add files or images to the textarea. When a file or image is deleted, it may still be stored in the context of the textarea, but the user does not notice this issue.
This plugin displays the orphaned files below the textarea, allowing the user to delete one or more selected files.
The admin can configure the plugin to show only a counter of orphaned files instead of a full file list. In this case, the user can use the "Manage Files" feature in the Tiny Editor.

This plugin integrates as a **TinyMCE toolbar button**.

## Features

- Detects unused (orphaned) files in TinyMCE draft areas
- Allows users to manually delete these files
- Simple integration into Moodle’s TinyMCE toolbar

## Installation

1. Clone or download this repository into your Moodle installation under: lib/editor/tiny/plugins/orphanedfiles

2. Run Moodle upgrade via CLI or through the admin web interface.

3. There are some settings for this plugin in the website administration. After installation you can activate or deactivate the plugin for
siteadmin or users. 

4. You also can activate that the plugin only shows a counter for existing orphaned files or
to show a table with a preview of the orphaned files and some filedata like size.


## Requirements

- Moodle 4.1 or higher
- TinyMCE editor must be enabled
- User permissions to edit content with file uploads

## Screenshots

*Add screenshots here if available.*

## Configuration



