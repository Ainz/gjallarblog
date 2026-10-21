Gjallarblog **0.1.0-alpha.2** improves the editor, input safety and journal navigation. It remains an experimental alpha.

- Corrects the sixteen interface-review findings, including pagination, readable fields, save/refresh behavior, menu settings guidance, image proportions and accessible navigation.
- Adds compact HTML formatting buttons, an unpublished sandboxed preview, and live character counters: **255 for titles, 10,000 for HTML source**.
- Filters entry HTML with HTML Purifier on save and display; preserves Unicode and quoted settings; rejects malformed input and retains text on ordinary validation errors.
- Improves mobile layout and light/dark administration styling.

All six combinations passed **44 integration + 21 helper + 56 usability/security checks each (726 check executions)**:

- Piwigo 15.7.0: PHP 8.2.34 and 8.3.35.
- Piwigo 16.4.0: PHP 8.2.34, 8.3.35, 8.4.26 and 8.5.11.
- Database: MariaDB 11.4.13.

Download **gjallarblog-0.1.0-alpha.2.zip**, verify **SHA256SUMS.txt**, and extract `gjallarblog/` into Piwigo's `plugins/` directory. Back up first. The automatic GitHub source archives are not the installable package.

**Upgrade note:** older HTML is filtered when displayed, without rewriting stored posts until saved. Scripts, inline styles and embeds no longer display. Existing over-limit posts remain readable but must be shortened to save. Settings saved by alpha.2 use JSON; restore the backed-up configuration if downgrading to alpha.1.

Known limitations include incomplete translations/theme coverage, no full visual editor or drafts, no automatic Evil_Blog migration and no independent security certification. The local demonstration banner and fixture posts are excluded from the package.

Original Evil_Blog attribution and history remain intact; HTML Purifier's LGPL notices and source are included. The repository is now [Ainz/gjallarblog](https://github.com/Ainz/gjallarblog).

See [installation and limitations](https://github.com/Ainz/gjallarblog/blob/main/README.md), [validation](https://github.com/Ainz/gjallarblog/blob/main/docs/VALIDATION.md), [review corrections](https://github.com/Ainz/gjallarblog/blob/main/docs/UI-CORRECTIONS.md), [changelog](https://github.com/Ainz/gjallarblog/blob/main/CHANGELOG.md) and [licensing](https://github.com/Ainz/gjallarblog/blob/main/NOTICE.md).
