# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-11-26

### Added
- Initial release
- Form field conversion (text, textarea, email, hidden, radio, checkbox, dropdown, password, date, number, file upload, submit)
- Automatic email task conversion to Convert Forms format
- Placeholder syntax conversion from Chronoforms to Convert Forms
- Support for any Joomla database prefix
- Creates unique form names with timestamps for repeated testing
- Detection of Chronoforms 6 and Convert Forms installations
- Admin interface showing form list with field and email task counts
- PHP code extraction for manual review

### Supported Field Types
- `field_text` -> `text`
- `field_textarea` -> `textarea`
- `field_email` -> `email`
- `field_hidden` -> `hidden`
- `field_radios` -> `radio`
- `field_checkbox` -> `checkbox`
- `field_checkboxes` -> `checkbox`
- `field_select` -> `dropdown`
- `field_dropdown` -> `dropdown`
- `field_button` -> `submit`
- `field_password` -> `password`
- `field_date` -> `datetime`
- `field_number` -> `number`
- `field_file` -> `fileupload`
- `field_upload` -> `fileupload`
