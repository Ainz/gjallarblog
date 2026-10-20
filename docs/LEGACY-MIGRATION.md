# Evil_Blog migration feasibility

Assessment: **feasible, but deliberately not included in the first alpha**. This work does not block the independent Gjallarblog release.

Inspected source: Evil_Blog SVN revision 32706 / official 1.2.4 and Ainz/evileblogupdate baseline d50cfc0. Both use `<prefix>evil_blog` with `id`, `date`, `header`, and `text`. Gjallarblog uses the same logical fields in `<prefix>gjallarblog`, with unsigned identifiers, utf8mb4 storage and a chronological index. Configuration is serialized under `evil_blog`; Gjallarblog has a separate `gjallarblog` key and normalizes legacy checkbox values.

## Proposed separate migration tool

1. Require a verified backup and administrator CSRF protection; offer a read-only dry run first.
2. Discover the actual table prefix and schema. Check IDs, title lengths, text encoding, dates, row count and unexpected columns before writing.
3. Require an empty destination for the first importer, preserving IDs and dates so existing `?/blog/id/N` links resolve. Refuse collisions rather than overwrite.
4. Copy and validate posts into utf8mb4. Stop on malformed or ambiguous encoding instead of silently losing characters. Validate totals and representative checksums.
5. Deserialize only arrays with `allowed_classes=false`; map supported configuration fields explicitly. Add the new menu-label default independently.
6. Audit copied administrator HTML and embedded resource links. Legacy HTML remains trusted HTML; migration must not imply that it becomes safe for untrusted authors.
7. Keep source tables and settings intact. Record an import marker and make repeat runs refuse duplicates. Support a rollback of the newly imported destination only.
8. Disable the old plugin before enabling Gjallarblog, because both use `?/blog` routes. Retain a database backup and staged upgrade test.

## Remaining evidence needed

A real, consented legacy database export has not been supplied. Historical versions may have schema/encoding differences; commented upstream code references possible `description` changes. A synthetic fixture matching the inspected schema is insufficient proof for all installations. Legacy image links, ExtendedDescription blocks and private-gallery expectations need evaluation.

Migration must be released and tested separately. Do not copy SQL manually into a populated Gjallarblog table or run both plugins together.