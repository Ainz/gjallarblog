# Follow-up after the first alpha

The alpha series establishes the journal/editor/RSS/menu feature set and the tested Piwigo 15/16 matrix. It is not a release candidate.

Before beta/RC: expand security review and static analysis; complete translations; broaden theme override and accessibility validation; test MySQL, alternate themes, rewrite/subdirectory installations and optional integrations; define versioned schema upgrades and rollback from this alpha; extend localization beyond the default date format, improve feed-relative links and consider export/purge behavior.

Evil_Blog import is a separate work stream, documented in LEGACY-MIGRATION.md. It does not block the independent alpha release and must not overwrite source data or a populated destination. Drafts, scheduling, comments, dedicated author roles and media management remain outside the first-alpha scope.

Before Piwigo Extensions Manager submission, review its publication requirements and clarify any remaining upstream provenance questions. Do not reuse the old extension identity 611 for automatic updates.
