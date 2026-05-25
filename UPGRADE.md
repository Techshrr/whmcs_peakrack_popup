# Upgrade Guide

This guide explains how to upgrade this module from an older version.

## Before upgrading

1. Back up the WHMCS files.
2. Back up the WHMCS database.
3. Make a copy of `modules/addons/peakrack_popup/`.
4. Review [CHANGELOG.md](CHANGELOG.md).
5. Check whether the upgrade includes database changes.

## Upgrade steps

1. Download the latest release from the official repository:

   https://github.com/Techshrr/whmcs_peakrack_popup

2. Replace the addon files in:

   `modules/addons/peakrack_popup/`

3. Keep existing uploaded images under `modules/addons/peakrack_popup/assets/images/` unless the release notes say otherwise.
4. Log in to the WHMCS admin area.
5. Open **Addons > PeakRack Popup** and verify popups, styles, and upload paths.
6. Clear the WHMCS template cache if client-area output does not update.

## Database migrations

This version does not require manual database migration.

The addon updates its database structure from module code when the admin page, frontend hook, upgrade hook, or cron file runs.

## Version-specific notes

### Upgrade from 1.5.x to 1.6.x

- No breaking changes.
- Existing popups and styles are preserved.
- Image uploads use `modules/addons/peakrack_popup/assets/images/`.

### Upgrade from 1.3.x to 1.4.x

- Legacy popup categories are no longer used by the admin UI or renderer.
- Existing database columns are kept for compatibility.

## Rollback

To roll back:

1. Restore the previous `modules/addons/peakrack_popup/` directory.
2. Restore the database backup if the upgrade changed module tables.
3. Restore uploaded images if they were changed.
4. Clear the WHMCS template cache.
5. Check the WHMCS activity log for errors.

## Notes

Do not overwrite production credentials, local configuration files, custom templates, callback secrets, or payment credentials unless the upgrade notes explicitly require it.