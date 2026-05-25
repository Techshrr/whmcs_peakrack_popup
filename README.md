# PeakRack Popup for WHMCS

> Official repository: https://github.com/Techshrr/whmcs_peakrack_popup
> License: MIT License

PeakRack Popup is a WHMCS addon for client-area popups and notices.

## Overview

The addon provides an admin page for creating Text, HTML, and Image popups shown in the WHMCS client area. It stores popup records, style presets, schedule rules, targeting rules, and event counters in module tables.

No WHMCS template files need to be edited. The frontend output is injected by a WHMCS hook and uses scoped `prp-` CSS classes.

## Features

- Admin CRUD for client-area popups.
- Text, HTML, and Image content types.
- Reusable style presets with display mode, theme, accent color, size, animation, custom CSS, and optional HTML templates.
- English and Chinese popup content fields.
- Audience rules for all visitors, guests, logged-in clients, and client group IDs.
- Page, language, weekday, URL phrase, unpaid invoice, product, product group, server, addon, TLD, and service due-date restrictions.
- Schedule windows, daily time ranges, frequency controls, global display limits, and per-client display limits.
- Optional image upload for JPG, PNG, GIF, and WEBP files up to 4 MB.
- Optional tracking endpoint for view, click, and close counters.
- Optional cron file for disabling expired popups.

## Requirements

- WHMCS 9.0.x
- PHP 8.3 or later
- MySQL 5.7 / 8.0

## Installation

1. Download the latest release from the official repository.
2. Upload the addon directory to:

   `modules/addons/peakrack_popup/`

3. Log in to the WHMCS admin area.
4. Go to **System Settings > Addon Modules** and activate **PeakRack Popup**.
5. Open **Addons > PeakRack Popup**, edit the disabled sample popup, or create a new popup.

## Configuration

| Option | Description | Default |
|---|---|---|
| Popup enabled | Controls whether an individual popup can render | Disabled for seeded sample |
| Content format | Selects Text, HTML, or Image content | Text |
| Language fields | Separate Chinese and English title/body/button values | Empty |
| Audience | Selects visitor scope | All visitors |
| Page rules | Matches client-area paths and simple patterns | `*` |
| Schedule and daily time range | Controls date and time availability | Empty |
| Frequency | Controls repeat behavior | Every visit |
| Display limits | Limits total or per-client display count | Empty |
| Permanent close | Allows close-button or checkbox-based permanent dismissal | Disabled |
| Style | Selects a reusable style preset | Default style |
| Image upload | Stores validated images under module assets | Empty |
| Tracking | Counts views, clicks, and closes through `track.php` | Enabled by frontend token |

## Usage

The administrator creates or edits a popup, selects its content type, audience, page rules, schedule, frequency, and style, then enables it. The frontend hook evaluates enabled popups for the current client-area request and renders the highest-priority matching popup.

For HTML popups, only trusted administrators should be allowed to edit content because HTML is rendered in the client area.

## Optional Cron

To disable expired popups in the database, run:

`php -q /path/to/whmcs/modules/addons/peakrack_popup/cron.php`

Frontend rendering already checks dates even without this cron task.

## Upgrade

See [UPGRADE.md](UPGRADE.md).

## Chinese Documentation

See [README.zh-CN.md](README.zh-CN.md).

## Security

Do not commit production credentials, API keys, database passwords, payment secrets, WHMCS license data, customer data, identity documents, or private signing keys.

To report a security issue, see [SECURITY.md](SECURITY.md).

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.
