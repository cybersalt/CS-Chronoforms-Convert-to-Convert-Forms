# Claude Code Instructions

## Joomla Development Reference

For Joomla development guidance, always refer to the main Joomla-Brain repository:

**https://github.com/cybersalt/Joomla-Brain**

Key documentation files:
- `JOOMLA3-COMPONENT-GUIDE.md` - Joomla 3 component development (used by this project)
- `JOOMLA3-PLUGIN-GUIDE.md` - Joomla 3 plugin development
- `JOOMLA5-PLUGIN-GUIDE.md` - Joomla 5 plugin development
- `JOOMLA5-CHECKLIST.md` - Joomla 5 compatibility checklist
- `COMPONENT-TROUBLESHOOTING.md` - Common issues and fixes
- `PACKAGE-BUILD-NOTES.md` - Building installable packages

## This Project

This is a **Joomla 3** component using the legacy MVC pattern (`JModelList`, `JViewLegacy`, `JControllerAdmin`).

### Key Technical Notes

1. **View's `$this->get('Xxx')` pattern**: Model methods must be named `getXxx()` for the view to call them via `$this->get('Xxx')`.

2. **Database prefix**: Always use `$db->getPrefix()` to get the configured prefix from `configuration.php`. Never use regex matching or hardcoded prefixes.

3. **Package building**: Use 7-Zip, not PowerShell's `Compress-Archive` which creates ZIPs missing directory entries.

## Company Information

- **Company:** Cybersalt Consulting Ltd.
- **Developer:** Tim Davis
- **Email:** tim@cybersalt.com
- **Website:** https://cybersalt.com
