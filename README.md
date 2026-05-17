# PeakRack Popup for WHMCS

PeakRack Popup is a WHMCS addon module for client-area popups and notices.

It is designed for WHMCS 9.x and PHP 8.3, and can be used for:

- limited-time server or resource package promotions
- coupon code campaigns with one-click copy
- new domain extension pricing notices
- network maintenance windows
- urgent announcements
- group-buy or bundle campaigns
- payment, service, support, or compliance notices

## Package Layout

This folder is an isolated upload package, similar to the existing gateway backup packages.

Upload the `modules` folder in this package to the root of a self-hosted WHMCS installation. It will place:

- `modules/addons/peakrack_popup/peakrack_popup.php`
- `modules/addons/peakrack_popup/hooks.php`
- `modules/addons/peakrack_popup/track.php`
- `modules/addons/peakrack_popup/lib/Popup.php`
- `modules/addons/peakrack_popup/whmcs.json`

## Installation

1. Upload this package's `modules` folder to the WHMCS root directory.
2. In WHMCS admin, go to **System Settings > Addon Modules**.
3. Activate **PeakRack Popup**.
4. Open **Addons > PeakRack Popup**.
5. Edit the disabled sample popup or create a new one.
6. Enable the popup after confirming its audience, page rules, and schedule.

Activation creates:

- `mod_peakrack_popups`
- `mod_peakrack_popup_events`

Deactivation keeps both tables so campaign history and counters are not lost.

## Main Features

- Admin CRUD for popups.
- Admin UI language switch between Chinese and English.
- Bilingual popup content fields for Chinese and English clients.
- Popup types: promotion, coupon, domain, maintenance, urgent, group-buy, and notice.
- Display modes: centered modal, top banner, bottom banner, and bottom-right floating popup.
- Audience rules: all visitors, guests only, logged-in clients only, or specific client group IDs.
- Page rules with simple patterns such as `*`, `cart.php`, or `clientarea.php?action=*`.
- Schedule windows with start and end times.
- Frequency controls: every visit, once per session, once per day, or once per browser.
- Per-popup accent color picker for the top bar, type label, coupon border, and CTA button.
- Coupon display with copy button.
- Optional image, CTA button, priority, delay, and auto-close.
- Basic counters for views, clicks, and closes.

## Bilingual Content

Each popup has separate fields for:

- Chinese title, body, and button text
- English title, body, and button text

The frontend follows the current WHMCS client-area language. If the client is using English, the popup uses the English fields. If the English fields are empty, the module falls back to the Chinese fields.

The admin language switch only changes the management interface; it does not change which client-side language is shown to visitors.

## Page Rules

Examples:

```text
*
cart.php
clientarea.php?action=services
clientarea.php?action=*
networkissues.php
```

If multiple enabled popups match the same page, only the highest-priority popup is shown.

## Tracking Endpoint

The frontend hook emits a signed token and sends lightweight events to:

```text
/modules/addons/peakrack_popup/track.php
```

The token is generated from the WHMCS encryption hash and the current date. The endpoint only updates local counters; it does not expose popup content management.

## Notes

- No WHMCS template files need to be edited.
- The frontend uses scoped CSS classes prefixed with `prp-`.
- The popup type label is rendered beside the title, and its wording follows the selected popup type.
- Button URLs are limited to HTTP(S), root-relative URLs, or common WHMCS PHP routes.
- Image URLs are limited to HTTP(S) or root-relative URLs.
- The disabled sample popup is safe to leave in place until you are ready to edit and enable it.
- Existing installs upgrading from older versions will receive the new English content columns and accent color column automatically when the addon page loads or when WHMCS runs the addon upgrade hook.

## Release Notes

### 1.2.1

- Keeps the flattened release package layout: upload the repository `modules` directory directly to the WHMCS root.
- Documents bilingual install and upgrade behavior for open-source distribution.

## License

MIT License. See [LICENSE](LICENSE).
