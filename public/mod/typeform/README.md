# Typeform Activity Module for Moodle

A Moodle activity module that integrates Typeform surveys into Moodle courses, allowing teachers to embed Typeform surveys and automatically track student completion.

## Description

The Typeform activity module enables seamless integration between Moodle and Typeform, providing a centralized way to manage and distribute surveys to students. Students can complete surveys directly within Moodle without leaving the platform, and completion is automatically tracked using Moodle's completion tracking system.

## Features

- **Embedded Surveys**: Display Typeform surveys directly within Moodle course pages
- **Automatic Completion Tracking**: Automatically mark activities as complete when students submit the survey
- **Workspace Management**: Filter and select forms from specific Typeform workspaces
- **Anonymous Student Code**: The `student_code` parameter is always anonymised (hashed); a per-activity setting controls whether it is sent to Typeform at all (GDPR compliance)
- **Centralized Management**: All surveys are created and managed in Typeform, while Moodle serves as the single access point for students
- **Privacy Compliant**: Only completion status is stored in Moodle; survey responses remain in Typeform
- **Multi-language Support**: Available in English, Spanish, Catalan, and Basque

## Requirements

- **Moodle Version**: 4.5 or later (compatible with Moodle 4.5 → 5.1 / 5.2)
- **PHP**: Version compatible with your Moodle installation
- **Typeform Account**: A Typeform account with API access
- **Typeform API Token**: A valid Typeform API token

## Installation

1. **Download the Plugin**
   - Download the plugin files or clone the repository
   - Place the `typeform` folder in your Moodle `mod/` directory

2. **Install via Moodle**
   - Log in as an administrator
   - Navigate to **Site administration** → **Notifications**
   - Follow the installation prompts
   - Click **Upgrade Moodle database now** when prompted

3. **Verify Installation**
   - Go to **Site administration** → **Plugins** → **Activity modules**
   - Verify that "Typeform" appears in the list of available modules

## Configuration

### Initial Setup

1. **Obtain Typeform API Token**
   - Log in to your Typeform account
   - Navigate to **Account Settings** → **API**
   - Generate a new API token or use an existing one
   - Copy the token

2. **Configure Plugin Settings**
   - Go to **Site administration** → **Plugins** → **Activity modules** → **Typeform**
   - Enter your **Typeform API Token** in the "Typeform API Token" field
   - (Optional) Enter **Allowed Workspaces** - comma-separated list of workspace IDs to restrict which forms can be used
   - Click **Save changes**

3. **Test Connection**
   - Click the **Test Connection** link in the plugin settings
   - Verify that the connection is successful and forms are being retrieved

### Workspace Configuration (Optional)

If you want to restrict which Typeform workspaces can be used:

1. In Typeform, identify the workspace IDs you want to allow
2. In Moodle plugin settings, enter the workspace IDs separated by commas
3. Only forms from these workspaces will be available when creating activities

## Usage

### Creating a Typeform Activity

1. **Enable Editing Mode** in your course
2. Click **Add an activity or resource**
3. Select **Typeform Survey**
4. Configure the activity:
   - **Name**: Enter a name for the activity
   - **Description**: (Optional) Add a description
   - **Select Typeform**: Choose a Typeform survey from the dropdown
   - **Include anonymous Student Code**: Check this box to include the (always anonymised) `student_code` parameter in the Typeform URL. Unchecked by default
5. Configure **Activity completion** settings if needed
6. Click **Save and return to course**

### Student Experience

1. Students click on the Typeform activity in the course
2. The Typeform survey is embedded directly in the Moodle page
3. Students complete the survey
4. Upon submission, the activity is automatically marked as complete
5. Students see a confirmation message

### Completion Tracking

- The activity is automatically marked as complete when a student submits the Typeform survey
- Completion status is tracked in Moodle's completion system
- Teachers can view completion status in the course completion report
- The activity supports Moodle's completion tracking features (automatic completion, completion prerequisites, etc.)

## Privacy and GDPR Compliance

### Data Storage

- **Moodle**: Only stores completion status (whether the user completed the survey)
- **Typeform**: Stores all survey responses (as configured in your Typeform account)

### Anonymous Student Code

The `student_code` parameter sent to Typeform is **always** anonymised: it is the
SHA-256 hash of the user id, never a plain identifier. The "Include anonymous
Student Code" setting only controls whether this anonymised value is included in
the form URL:

- **Enabled**: the anonymised `student_code` is included in the Typeform URL.
- **Disabled (default)**: no `student_code` is sent to Typeform at all.

In all cases Moodle only tracks completion status; survey responses live in Typeform.

### Privacy API

The module implements Moodle's Privacy API, allowing users to:
- Export their completion data
- Delete their completion data
- View what data is stored about them

## Capabilities

The module defines two capabilities:

- **mod/typeform:view**: Allows users to view Typeform activities
  - Default: All users (guest, student, teacher, manager)
  
- **mod/typeform:addinstance**: Allows users to add new Typeform activities
  - Default: Editing teachers and managers only

## Troubleshooting

### Forms Not Loading

- Verify your API token is correct
- Check that the API token has the necessary permissions
- Use the "Test Connection" feature to diagnose issues
- Ensure your server can make outbound HTTPS connections to `api.typeform.com`

### Completion Not Tracking

- Verify that activity completion is enabled for the course
- Check that the activity has completion tracking enabled
- Ensure JavaScript is enabled in the student's browser
- Check browser console for JavaScript errors

### Workspace Issues

- Verify workspace IDs are correct (no spaces, comma-separated)
- Ensure the API token has access to the specified workspaces
- Leave the field empty to allow all workspaces

## Support

For issues, questions, or contributions:

- **Website**: [https://www.tresipunt.com](https://www.tresipunt.com)
- **Email**: contacte@tresipunt.com

## Version Information

- **Current Version**: 1.0
- **Maturity**: Stable
- **Release Date**: January 2026

## License

This plugin is licensed under the GNU General Public License v3.0.

## Copyright

Copyright © 2026 3ipunt - [https://www.tresipunt.com](https://www.tresipunt.com)

## Changelog

### Version 1.0 (January 2026)
- Initial stable release
- Embedded Typeform surveys
- Automatic completion tracking
- Workspace filtering
- Anonymous survey support
- Privacy API implementation
- Multi-language support (English, Spanish, Catalan, Basque)

## Known Limitations

- Survey responses are not displayed in Moodle (they remain in Typeform)
- No grading integration (surveys are not graded activities)
- Surveys cannot be created or edited from within Moodle
- Migration of existing surveys is not supported

## Future Enhancements

Potential features for future versions:
- Response reporting within Moodle
- Grading integration
- Survey creation/editing from Moodle
- Migration tools for existing surveys

---

**Note**: This module requires an active Typeform account and API access. Survey creation and management must be done in Typeform; Moodle serves as the distribution and tracking platform.

