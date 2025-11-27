# CF6 Convert - Chronoforms 6 to Convert Forms Converter

A Joomla 3 component that converts Chronoforms 6 forms to Convert Forms format to prepare for Joomla 4 migration.

## Purpose

This component was created to help Joomla 3 site administrators migrate their forms from Chronoforms 6 to Convert Forms **before** upgrading to Joomla 4. Since Chronoforms 6 is not compatible with Joomla 4, converting forms to Convert Forms (which supports both Joomla 3 and 4) allows for a smoother migration path.

## Compatibility

### Tested With

| Extension | Type | Version | Developer |
|-----------|------|---------|-----------|
| **Joomla** | CMS | 3.x | Joomla Project |
| **ChronoForms6** | Component | 6.1.4 (June 2019) | Chronoman / ChronoEngine.com |
| **ChronoForms6** | Plugin (content) | 6.0 (April 2017) | ChronoEngine.com |
| **ChronoForms6 Package** | Package | 6.1.2 (May 2019) | ChronoEngine.com Team |
| **Convert Forms** | Component | 4.2.2 | Tassos.gr |

### Requirements

- **Joomla 3.x** (this component is for Joomla 3 only)
- **Chronoforms 6** installed (source of forms to convert)
- **Convert Forms** installed (destination for converted forms)
- **PHP 7.2+**

## Features

- Converts form fields (text, textarea, email, radio, checkbox, dropdown, etc.)
- Automatically converts email notifications/tasks
- Converts placeholder syntax from Chronoforms to Convert Forms format
- Extracts custom PHP code for review
- Creates unique form names to allow repeated conversions for testing
- Works with any database prefix configured in Joomla

## Installation

1. Download the latest release ZIP file from the [Releases page](https://github.com/cybersalt/CS-Chronoforms-Convert-to-Convert-Forms/releases)
2. In Joomla Administrator, go to **Extensions > Manage > Install**
3. Upload and install the ZIP file
4. Navigate to **Components > CF6 to ConvertForms**

## Usage

### Step-by-Step Guide

1. **Backup your database** before converting any forms
2. Go to **Components > CF6 to ConvertForms** in your Joomla administrator
3. You'll see a list of all your Chronoforms 6 forms with:
   - Form title and alias
   - Published status
   - Field count
   - Email task count
4. Select the forms you want to convert using the checkboxes
5. Click the **Convert** button in the toolbar
6. The forms will be imported into Convert Forms
7. Go to **Components > Convert Forms** to see your imported forms
8. Review and test each converted form before going live

### Migration Workflow

For a complete Joomla 3 to Joomla 4 migration:

1. Install Convert Forms on your Joomla 3 site
2. Install this CF6 Convert component
3. Convert all your Chronoforms to Convert Forms
4. Test the converted forms thoroughly
5. Update any module/menu positions pointing to old Chronoforms
6. Once satisfied, proceed with your Joomla 4 upgrade
7. Convert Forms will continue working on Joomla 4

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
- **Form Styling:** CSS classes are preserved, but you may need to adjust styling for Convert Forms
- **Backup:** Always backup your database before converting forms!

## What's NOT Converted

- reCAPTCHA settings (must be reconfigured)
- Complex conditional logic rules
- Custom JavaScript behaviors
- Third-party integrations (payment gateways, CRM connections, etc.)
- File upload destination paths (review these in Convert Forms)

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

## Related Resources

- [Convert Forms Documentation](https://www.tassos.gr/joomla-extensions/convert-forms/docs)
- [Joomla 3 to 4 Migration Guide](https://docs.joomla.org/Joomla_3.x_to_4.x_Step_by_Step_Migration)
