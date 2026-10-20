# Changelog

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