# CF6 Convert - Chronoforms 6 to Convert Forms Converter

A Joomla 3 component that converts Chronoforms 6 forms to Convert Forms format.

## Description

CF6 Convert allows you to migrate your existing Chronoforms 6 forms to Convert Forms with a single click. The component reads your Chronoforms directly from the database and creates equivalent forms in Convert Forms.

## Features

- Converts form fields (text, textarea, email, radio, checkbox, dropdown, etc.)
- Automatically converts email notifications/tasks
- Converts placeholder syntax from Chronoforms to Convert Forms format
- Extracts custom PHP code for review
- Creates unique form names to allow repeated conversions for testing
- Works with any database prefix configured in Joomla

## Requirements

- Joomla 3.x
- Chronoforms 6 installed (source of forms to convert)
- Convert Forms installed (destination for converted forms)
- PHP 7.2+

## Installation

1. Download the latest release ZIP file
2. In Joomla Administrator, go to Extensions > Manage > Install
3. Upload and install the ZIP file
4. Navigate to Components > CF6 to ConvertForms

## Usage

1. Go to **Components > CF6 to ConvertForms** in your Joomla administrator
2. You'll see a list of all your Chronoforms 6 forms
3. Select the forms you want to convert using the checkboxes
4. Click the **Convert** button in the toolbar
5. The forms will be imported into Convert Forms
6. Go to **Components > Convert Forms** to see your imported forms

## What Gets Converted

### Fields
- Text fields
- Textarea fields
- Email fields
- Hidden fields
- Radio buttons
- Checkboxes
- Dropdown/Select fields
- Password fields
- Date fields
- Number fields
- File upload fields
- Submit buttons

### Email Tasks
Email notifications are automatically converted with:
- Recipients
- Subject lines
- From name and email
- Reply-to addresses
- Email body content
- Placeholder conversion (e.g., `{data:fieldname}` becomes `{field.fieldname}`)

### Placeholders Converted
| Chronoforms 6 | Convert Forms |
|---------------|---------------|
| `{data:fieldname}` | `{field.fieldname}` |
| `{var:fieldname}` | `{field.fieldname}` |
| `{_site_name}` | `{site.name}` |
| `{_site_url}` | `{site.url}` |
| `{_site_email}` | `{site.email}` |
| `{_user_name}` | `{user.name}` |
| `{_user_email}` | `{user.email}` |

## Important Notes

- **reCAPTCHA:** You'll need to configure reCAPTCHA separately in Convert Forms if your forms use it
- **Conditional Logic:** Complex conditional logic may need manual adjustment after conversion
- **Custom PHP:** Any custom PHP code will be extracted but may need modification for Convert Forms
- **Backup:** Always backup your database before converting forms!

## Author

**Cybersalt Consulting Ltd.**
- Developer: Tim Davis
- Email: tim@cybersalt.com
- Website: https://cybersalt.com

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## Support

For issues and feature requests, please use the [GitHub Issues](https://github.com/cybersalt/CS-Chronoforms-Convert-to-Convert-Forms/issues) page.
