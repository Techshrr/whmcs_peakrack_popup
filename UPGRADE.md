# Upgrade Notes

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
