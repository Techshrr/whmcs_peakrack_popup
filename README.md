# PeakRack Popup for WHMCS

PeakRack Popup is a WHMCS addon module for client-area popups and notices.

It is designed for WHMCS 9.x and PHP 8.3. The popup model follows the official-style `Text`, `HTML`, and `Image` content types rather than business categories.

It can be used for:

- text notices with a title, body, and optional button
- trusted administrator HTML content
- image-led modal or poster campaigns
- network maintenance windows
- payment, service, support, or compliance notices

## Package Layout

The repository root is intentionally shallow for GitHub browsing. The deployable addon is the `peakrack_popup` directory.

Upload or copy `peakrack_popup` to `modules/addons/peakrack_popup/` in a self-hosted WHMCS installation. It will place:

- `modules/addons/peakrack_popup/peakrack_popup.php`
- `modules/addons/peakrack_popup/hooks.php`
- `modules/addons/peakrack_popup/track.php`
- `modules/addons/peakrack_popup/cron.php`
- `modules/addons/peakrack_popup/lib/Popup.php`
- `modules/addons/peakrack_popup/assets/images/`
- `modules/addons/peakrack_popup/whmcs.json`

## Installation

1. Upload this addon directory to WHMCS:

   ```text
   peakrack_popup/ -> modules/addons/peakrack_popup/
   ```

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
- Independent Styles manager with reusable popup style presets.
- Admin UI language switch between Chinese and English.
- One-click admin preview.
- Archive and restore workflow.
- Bilingual popup content fields for Chinese and English clients.
- Popup types: Text, HTML, and Image with mode-specific required fields.
- Popups can select a reusable Style. Styles can define display mode, theme, accent color, size, animation, scoped custom CSS, and optional HTML templates.
- Display modes: modal with overlay, poster modal, modal without overlay, top banner, bottom banner, bottom-right, bottom-left, right-side, and left-side floating popup.
- Popup sizing controls and fade, slide, or no-animation options.
- Audience rules: all visitors, guests only, logged-in clients only, or specific client group IDs.
- Page rules with simple patterns such as `*`, `cart.php`, or `clientarea.php?action=*`.
- Language, weekday, URL-contains, unpaid-invoice, active product, product group, server, addon, TLD, missing product, missing addon, and missing TLD restrictions.
- Service due-date restrictions for before, on, or after the next due date with configurable day comparisons.
- Schedule windows with start and end times.
- Daily time ranges.
- Frequency controls: every visit, once per session, once per day, or once per browser.
- Global display limits and per-client display limits.
- Permanent close options: disabled, close button, or checkbox confirmation.
- Per-popup accent color picker for the top bar, emphasis elements, and CTA button.
- Optional image URL or uploaded image, CTA button, priority, delay, and auto-close with a small frontend countdown hint.
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
- When auto-close seconds are configured, the frontend shows a subtle live countdown such as `Auto-closes in 10s`.
- Text content requires at least one title or body field. HTML content requires a body field. Image content requires an image URL or uploaded image; title, body, and button are optional.
- Legacy business categories such as promotion, coupon, or notice are no longer used by the admin UI or frontend renderer. The old database columns are kept only for upgrade compatibility.
- Button URLs are limited to HTTP(S), root-relative URLs, or common WHMCS PHP routes.
- Image URLs are limited to HTTP(S), root-relative URLs, or the module upload path. Admin uploads are stored under `modules/addons/peakrack_popup/assets/images/`; supported formats are JPG, PNG, GIF, and WEBP up to 4 MB.
- The disabled sample popup is safe to leave in place until you are ready to edit and enable it.
- Existing installs upgrading from older versions will receive new database columns automatically when the addon page loads, when the frontend hook runs, or when WHMCS runs the addon upgrade hook.

## Styles

Open **Addons > PeakRack Popup > Styles** to manage reusable popup styles. The addon seeds several editable styles such as centered modal, poster image, top banner, bottom banner, right corner, and plain center.

Custom CSS supports the `{root}` placeholder, which is replaced with the current popup root selector. Example:

```css
{root} .prp-panel{border-radius:4px}
{root} .prp-button{text-transform:uppercase}
```

HTML templates are optional. Leave them blank to use the default renderer, or use placeholders such as `{close}`, `{image}`, `{title}`, `{body}`, `{actions}`, `{image_close}`, `{content}`, and `{accent}` for advanced style layouts.

## Optional Cron

The frontend already respects end dates at render time. If you also want expired popups to be marked disabled automatically in the database, add:

```text
php -q /path/to/whmcs/modules/addons/peakrack_popup/cron.php
```

Run it every 5 to 15 minutes from the WHMCS server cron user.

## Original Implementation

This addon was designed from public product and documentation feature descriptions for a WHMCS client-area popup manager. It does not copy proprietary ModulesGarden source code, templates, branding, license checks, or encoded files.

## Release Notes

### 1.2.1

- Documented the earlier flat `modules` release layout before the 1.2.2 package-directory normalization.
- Documents bilingual install and upgrade behavior for open-source distribution.

### 1.2.2

- Normalized the open-source repository layout under the `whmcs_peakrack_popup/` deploy package directory.
- Updated installation documentation so downloads and git clones use the same upload path.

### 1.2.3

- Flattened the GitHub repository layout so `peakrack_popup/` is visible at the root.
- Updated installation and upgrade documentation for the direct addon-folder layout.

### 1.2.4

- Refined the client-area popup presentation for a cleaner WHMCS/Lagom-friendly look.
- Reduced heavy borders, shadow, and overlay weight while keeping the popup visible.
- Moved the popup type label above the title, softened the close button, and improved CTA spacing.
- Improved responsive behavior for modal, banner, and corner popup modes without changing database tables.

### 1.2.5

- Added a subtle frontend countdown hint when auto-close seconds are configured.
- The countdown follows the client-area language and updates once per second.
- No database changes are required.

### 1.3.0

- Added marketplace-style campaign controls: preview, archive/restore, trusted HTML/image content modes, permanent close, display limits, more popup positions, sizing, and animations.
- Added advanced targeting for language, weekday, URL phrases, unpaid invoices, products, product groups, servers, addons, TLDs, missing catalog ownership, daily time windows, and service due dates.
- Added optional cron support to disable expired popups in the database while keeping render-time date checks.

### 1.3.1

- Added mode-specific admin validation for Text, HTML, Image, and the then-existing legacy Coupon configuration.
- Fixed image-led popups so they render as image-first popups instead of inheriting coupon UI from stale coupon fields.
- Coupon codes are now cleared, hidden, and ignored whenever Image content format is selected.

### 1.4.0

- Switched the module to official-style popup type semantics: `Text`, `HTML`, and `Image`.
- Removed promotion/coupon/domain/maintenance/urgent/group-buy business categories from the admin form, validation, list summary, and frontend renderer.
- Removed the dedicated coupon UI. Coupon text can still be written into Text or HTML content manually when needed.

### 1.5.0

- Added an independent Styles manager inspired by the official Styles workflow.
- Seeded reusable styles for centered modal, poster image, top banner, bottom banner, right corner, and plain center.
- Popups can now select a reusable Style; selected Styles override display mode, theme, accent color, size, animation, scoped custom CSS, and optional HTML templates.

### 1.6.0

- Added admin image uploads for Image popups while keeping manual Image URL support.
- Uploaded images are validated by extension and MIME type, limited to JPG, PNG, GIF, or WEBP up to 4 MB, and stored in the addon assets directory.
- Saving an uploaded image automatically fills the popup's Image URL with the module asset path.

### 1.6.1

- Improved image-only popups with a visible top close button, a full-width bottom close button, Escape-key close, and overlay-click close.
- Changed image popup media surfaces to white, ratio-preserving containers so uploaded artwork no longer shows dark side bars from the popup shell.
- Reduced accidental cropping in poster image mode and hid the decorative poster curve when there is no text content below the image.

Detailed upgrade notes: [UPGRADE.md](UPGRADE.md).

## License

MIT License. See [LICENSE](LICENSE).
