# Changelog

## Unreleased

- Insert pasted or selected images into the entry immediately with a safe default layout; open image layout controls only when requested from the image list, with both close and cancel controls.

## 0.06-beta.1 — 2026-10-22

- Use the theme's standard menu markup without a custom class, active-state styling, title tooltip or plugin menu stylesheet.
- Hide database entry IDs from public pages; permanent entry URLs remain available by opening an entry.
- Explain Piwigo Restore separately from plugin-folder removal and optional permanent data erasure.
- Clarify where full recent entries appear and cap the shared home/album/journal count at five.
- Replace free-form date format input with three common choices, show the current formatted date and timezone, and link to the PHP format reference.
- Keep the beta channel and increment its prerelease counter for this replacement candidate.

## 0.06-beta — 2026-10-22

- Use one plain-text journal name for the menu item, journal headings and return link; strip attempted HTML formatting from journal and entry titles.
- Add previous/next entry navigation, use button-style admin shortcuts, and move removal controls into a dedicated tab with clear Piwigo Restore behavior.
- Prevent hover bolding from shifting the Gjallarblog menu item. Remove the obsolete draft-status sentence from formatting help.
- Advance the local beta version without a prerelease counter, preserving the two-component version scheme and forward upgrade ordering.

## 0.05-beta.1 — 2026-10-22

- Add gallery-timezone date/time selection for historical entries; reject invalid and future dates.
- Add clipboard paste and file upload for PNG, JPEG and WebP images, with server validation, metadata-stripping re-encoding, captions, alternative text, size and placement controls.
- Serve media through Piwigo routes; preserve it on deactivation/reinstall and include it in explicit verified erasure.
- Expand theme-aware image layout styling and safe formatting help for headings, rules, quotations and code.

## 0.04-alpha.1 — 2026-10-21

- Group settings with consistent controls and help; add before/after home and album placement and journal link visibility.
- Remove journal RSS links and redirect the legacy feed address to Piwigo’s core feed.
- Add webmaster-only removal review with entry count, explicit confirmation, optional permanent data erasure and actual folder-removal verification.
- Preserve posts/settings for native deactivation, uninstall and delete; reject read-only removal before changing plugin state.

## 0.03-alpha.1 — 2026-10-21

- Adopt Major.Minor version strings with at least two minor digits, plus explicit alpha/beta/rc identifiers.
- Preserve both published 0.1.0 alpha releases, tags and assets. New version sorts above both under Piwigo's own comparator.
- Derive package and publishing identifiers from main.inc.php; reject three-component numeric versions and inconsistent release metadata.
- Document Piwigo update behavior and test real core upgrade handling with preserved data. Journal/editor behavior remains as tested in 0.1.0-alpha.2.

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
