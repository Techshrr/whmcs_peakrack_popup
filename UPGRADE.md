# Upgrade Notes

## 1.6.1

- Frontend presentation update only: no database changes are required.
- Image popups now have clearer close affordances: top close button, bottom close button, Escape key, and overlay click.
- Image and poster media now use white, ratio-preserving containers to avoid dark side bars and unwanted artwork cropping.
- Manual update: copy `peakrack_popup/` to `modules/addons/peakrack_popup/` and overwrite the existing addon files. Keep any runtime images already uploaded under `assets/images/`.
- Addon version bumped to `1.6.1`.

## 1.6.0

- Admin behavior update only: no database changes are required.
- Image popups now support either a manual Image URL or an uploaded image file.
- Uploaded images are stored in `modules/addons/peakrack_popup/assets/images/`. Make sure the addon directory is writable by the web server if you want to use uploads.
- Existing uploaded images live in the installed WHMCS addon directory and are runtime data. Keep them when replacing addon files.
- Manual update: copy `peakrack_popup/` to `modules/addons/peakrack_popup/` and overwrite the existing addon files.
- Addon version bumped to `1.6.0`.

## 1.5.0

- Database migration release. The addon creates `mod_peakrack_popup_styles` and adds `style_id` to popups automatically.
- A default set of reusable styles is seeded when the addon page or frontend hook runs.
- Popups can select a reusable Style. Selected Styles override display mode, theme, accent color, size, animation, scoped custom CSS, and optional HTML templates.
- Manual update: copy `peakrack_popup/` to `modules/addons/peakrack_popup/` and overwrite the existing addon files.
- Addon version bumped to `1.5.0`.

## 1.4.0

- Admin and frontend behavior update only: no database changes are required.
- Popup `Type` now follows the official-style content model: `Text`, `HTML`, or `Image`.
- Legacy business categories such as promotion, coupon, domain, maintenance, urgent, and group-buy are no longer shown or used.
- The dedicated coupon field and coupon renderer were removed. Add coupon text directly inside Text or HTML content if needed.
- Manual update: copy `peakrack_popup/` to `modules/addons/peakrack_popup/` and overwrite the existing addon files.
- Addon version bumped to `1.4.0`.

## 1.3.1

- Frontend rendering and admin-validation update only: no database changes are required.
- Image content format now requires a valid image URL and renders as an image-first popup. Title, body, and button fields remain optional.
- Legacy coupon-specific handling was deprecated and is fully removed in `1.4.0`.
- Manual update: copy `peakrack_popup/` to `modules/addons/peakrack_popup/` and overwrite the existing addon files.
- Addon version bumped to `1.3.1`.

## 1.3.0

- Database migration release. The addon automatically adds the new campaign, targeting, archive, sizing, animation, display-limit, and due-date columns when the addon page or frontend hook runs.
- Manual update: copy `peakrack_popup/` to `modules/addons/peakrack_popup/` and overwrite the existing addon files.
- Optional cron: add `php -q /path/to/whmcs/modules/addons/peakrack_popup/cron.php` if you want expired popups to be marked disabled in the database.
- Review raw HTML popups after upgrade. HTML content is intentionally rendered as trusted administrator content.
- Addon version bumped to `1.3.0`.

## 1.2.5

- Frontend behavior update only: no database changes are required.
- Auto-close popups now show a subtle live countdown in the client area.
- Manual update: copy `peakrack_popup/` to `modules/addons/peakrack_popup/` and overwrite the existing addon files.
- Addon version bumped to `1.2.5`.

## 1.2.4

- Frontend presentation update only: no database changes are required.
- Manual update: copy `peakrack_popup/` to `modules/addons/peakrack_popup/` and overwrite the existing addon files.
- If a popup does not reappear during testing, clear the browser's local storage for the WHMCS site or temporarily set the popup frequency to every page.
- Addon version bumped to `1.2.4`.

## 1.2.3

- Repository layout only: the deployable addon now lives at repository root as `peakrack_popup/`.
- Existing WHMCS installs do not need database changes for this release.
- When updating manually, copy `peakrack_popup/` to `modules/addons/peakrack_popup/`.
- Addon version bumped to `1.2.3`.

## 1.2.2

- Repository layout only: deployable files now live under `whmcs_peakrack_popup/modules`.
- Existing WHMCS installs do not need database changes for this release.
- When updating manually, copy the new `whmcs_peakrack_popup/modules` directory contents over your WHMCS root.
- Addon version bumped to `1.2.2`.

## 1.2.1

- Kept the flattened release package layout.
- Documented bilingual install and upgrade behavior for open-source distribution.
