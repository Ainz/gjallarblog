# Gjallarblog alpha.2 — interface corrections

21 October 2026. The sixteen findings from the alpha.1 review are addressed as follows. This records implementation and observed checks, not a full accessibility certification.

| Finding | Correction and verification |
|---|---|
| 01 Calendar errors | Dedicated journal routes no longer inherit photo-calendar actions. Gallery photo controls remain separate. HTTP checks pass on Piwigo 15 and 16. |
| 02 Pagination | Scoped layout, 36 × 40 px measured targets, visible current page and focus. Public widths 320–1280 tested. |
| 03 Contrast | Explicit readable fields/help/errors in both core light and dark admin themes; dark fields use #f5f5f5 on #333. |
| 04 Save context | HTTP 303 after successful writes; locate the saved entry's true list page; one-time success message. Refresh does not repeat the write. |
| 05 Validation | Unicode character limits, field errors, preserved text, blank-menu fallback and JSON-safe Unicode settings. Malformed types and invalid characters rejected. |
| 06 Menu discoverability | One plain-text Journal name is used for menu, heading and return links; visibility and position remain controlled separately. The menu hover keeps stable text weight. |
| 07 Dates | Readable dates, localized default j F Y, numeric-format guidance, example and server timezone. Custom formats retain PHP behavior. |
| 08 Authoring | Formatting buttons, collapsed HTML help and sandboxed unpublished preview. No new editor dependency or draft system. |
| 09 Actions | Publish/Save primary actions; separate permanent-delete section, explicit checkbox and entry-specific confirmation. |
| 10 Existing entries | Jump link, Edit cue and collapsed new-entry form on later pages. Saved entry opens automatically. The Entries/Settings panels align left beneath their tabs, with a 980 px maximum width. |
| 11 Images | Responsive max-width and automatic height replace blanket cropping. Image proportions follow their source. Broader portrait/diagram/theme combinations still merit manual evaluation. |
| 12 Location | Journal/entry breadcrumbs, active Blog section and top/bottom return links to the correct journal page. |
| 13 Semantics | H1 on dedicated pages, contextual entry links, named pagination and aria-current state. Browser accessibility tree checked; screen-reader walkthrough remains pending. |
| 14 Vocabulary | Consistent Entries/Settings, translated new UI/validation strings and configured journal name in return links. Translation coverage remains incomplete. |
| 15 Long home pages | Explicit full-entry placement wording and scrolling warning. Keep the existing checkbox; no summary-mode feature expansion. Ten long/short fixtures make the behavior evaluable. |
| 16 Reading polish | 16 px body text, 1.75 line height, restrained spacing, smaller-screen wrapping, context-independent album link in demonstration copy. |

## Input and content limits

Title: 255 Unicode code points. Body: 10,000 code points of HTML source, including tags. Both have live counters and browser validation, backed by server validation. Confirmed 10,000 accepted / 10,001 rejected in the browser and HTTP tests. A multi-code-point emoji can count as several characters. Existing longer posts stay readable and must be shortened before saving.

The authoring surface accepts a limited set of HTML elements through HTML Purifier 4.19.1. HTML filtering also protects legacy stored bodies when displayed and applies to RSS and preview. Unsupported active content, event attributes, styles and URL schemes are removed. Plain-text titles/settings are escaped for their output context. IDs, actions, checkbox values, UTF-8, control characters, field shapes and lengths are validated; writes require administrator access and CSRF tokens.

## Formatting choice

[Piwigo EditorPlus](https://github.com/Piwigo/piwigo-editorplus) provides Quill/CKEditor integrations with specific administration pages and textareas. Its inspected integration does not automatically bind to Gjallarblog. This alpha therefore supplies seven small HTML insertion buttons and a safe preview without forcing an editor plugin or coupling to its internals. This is not WYSIWYG. An optional adapter can be evaluated later if author feedback warrants it.

The earlier comparison with [Ghost preview](https://ghost.org/help/publishing-content/), [WordPress post settings](https://wordpress.org/documentation/article/page-post-settings-sidebar/) and [Piwigo Additional Pages](https://doc.piwigo.org/customizing-your-gallery/add-pages-to-piwigo-gallery/) informed preview, explicit actions and simple placement guidance. No subscriptions, comments, tags, scheduling or large publishing dashboard were added.

## Evidence and limitations

Six runtime combinations passed 44 integration, 21 helper and 56 usability/security checks each (726 executions). Exact versions and evidence are in VALIDATION.md and test-evidence/. Public journal and editor reflow were checked at 320, 390, 768 and 1280 CSS pixels without horizontal page overflow; pagination targets measured 36 × 40 px. Light/dark admin, formatting insertion, live counter boundaries and preview were exercised in the browser.

Ten additive local entries alternate shorter and longer Lorem Ipsum content: 739, 8,652, 774, 5,588, 570, 8,812, 501, 7,410, 673 and 10,000 submitted source characters. One title has 255 characters. Sanitizer normalization can slightly change saved source length. The seven pre-existing entries were retained; only the exact misleading sentence in the original demonstration welcome entry was corrected. User-authored content and gallery branding were preserved.

Remaining alpha evaluation: another public theme, non-English completion, screen-reader and zoom-specific checks, MySQL, rewrite/subdirectory deployment, optional ExtendedDescription, comprehensive sanitizer interaction review and Evil_Blog migration. No claim of RC readiness or security certification.
