# Changelog

All notable changes to this project are documented in this file.

This project follows Semantic Versioning where practical.

## [1.6.1] - 2026-05-21

### Fixed

- Improved image-only popups with visible close controls, Escape-key close, and overlay-click close.
- Reduced accidental image cropping in poster image mode.

## [1.6.0] - 2026-05-21

### Added

- Added admin image uploads for Image popups.
- Validated uploaded images by extension and MIME type, limited to JPG, PNG, GIF, or WEBP up to 4 MB.

## [1.5.0] - 2026-05-21

### Added

- Added an independent Styles manager with reusable styles.
- Allowed popups to select a reusable style that controls display mode, theme, accent color, size, animation, custom CSS, and optional HTML templates.

## [1.4.0] - 2026-05-21

### Changed

- Switched popup content semantics to Text, HTML, and Image.
- Removed legacy business-category UI from the admin form and frontend renderer.

## [1.3.0] - 2026-05-21

### Added

- Added preview, archive/restore, trusted HTML/image content modes, permanent close, display limits, popup positions, sizing, animations, advanced targeting, service due-date rules, and optional cron support.

## [1.2.3] - 2026-05-21

### Changed

- Flattened the repository layout so `peakrack_popup/` is visible at the repository root.

## [1.0.0] - 2026-05-21

### Added

- Initial release.
- Added client-area popup management, rendering hook, tracking endpoint, and module tables.