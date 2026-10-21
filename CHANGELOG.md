# Changelog

## 0.1.0-alpha.2 — 2026-10-21

- Correct all sixteen findings from the first interface review: journal-only navigation, pagination targets, administration contrast, post-save redirects, inline errors, menu guidance, localized default dates, preview/help, action hierarchy, entry-list access, uncropped images, breadcrumbs, accessible link/state names, consistent translated labels, placement guidance and reading/sample-copy polish.
- Add title (255) and HTML-source body (10,000) character limits with Unicode counters and matching server validation; retain submitted text on validation errors.
- Add compact HTML formatting buttons and a sandboxed, unpublished preview.
- Filter HTML on save, preview, public display and RSS using bundled HTML Purifier 4.19.1 (LGPL-2.1-or-later); reject invalid UTF-8, malformed field shapes and unsupported control characters.
- Preserve Unicode and quoted settings through escaped JSON storage, while reading legacy settings. Capture inserted IDs before restoring the database connection charset.
- Improve narrow-screen editor layout and respect light/dark Piwigo administration colors.
- Rename the existing GitHub repository to lowercase `gjallarblog`; retain its history and alpha.1 release.
- Validate six Piwigo 15/16 and PHP 8.2–8.5 combinations: 44 integration, 21 helper and 56 usability/security checks per combination.

## 0.1.0-alpha.1 — 2026-10-20

First Gjallarblog alpha, derived from Evil_Blog by EvilKant through Ainz/evileblogupdate; inherited history and attribution retained.

- Restore missing administration template and clean damaged CSS.
- Separate plugin identity, settings and storage from Evil_Blog.
- Validate identifiers, pagination, UTF-8 and field limits; require administrator access and CSRF tokens for changes.
- Escape titles and editor fields; retain HTML bodies for trusted administrators.
- Replace legacy RSS dependency with bounded XML output, removing forbidden XML control characters.
- Preserve posts and settings across plugin disable, uninstall and reinstall.
- Add independent Blog menu block, configurable menu label and Piwigo menu positioning.
- Bound pagination navigation size; guard optional ExtendedDescription integration.
- Add reproducible local packaging and isolated compatibility tests.

Alpha limitations and tested combinations are documented in README.md and docs/VALIDATION.md. No automatic Evil_Blog migration is included.