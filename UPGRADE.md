# Upgrade Notes

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
