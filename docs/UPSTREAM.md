# Verified upstream provenance

Recorded 20 October 2026.

- Historical repository: https://github.com/Ainz/evileblogupdate (private at assessment).
- Baseline: d50cfc0ca0bc88568a309dee0fc5f9b2d33df6bd, main, 17 January 2024.
- Original plugin: https://piwigo.org/ext/index.php?eid=611
- Historical SVN: https://piwigo.org/svn/!svn/bc/32706/extensions/Evil_Blog/
- Translation source: https://piwigo.org/svn/!svn/bc/33315/extensions/Evil_Blog/
- Original 1.2.4 archive: https://piwigo.org/ext/download.php?rid=8046
- Plugin archive SHA-256: 2c40c51f6d9fac19b3acabb39f8f3f72aca68f957f8e132587a1b08a9d6c071f

The original restoration started as a real clone retaining the original 32 commits. The Gjallarblog alpha continues that history in Ainz/Gjallarblog. Ainz/evileblogupdate is retained as the historical source and will receive a redirect before archiving.

All 79 private baseline files matched their Git blob hashes. All 47 common PHP files match SVN 32706 after comments/whitespace and line-ending normalization. Release 1.2.4 matches 77 files from that revision, changes the main version header, and adds packaging metadata. Current SVN adds four languages and a Polish key.

Initial restoration: restore manage.tpl from SVN 33315 (identical to the release); remove copied line numbers in admin.css; refresh he_IL/is_IS/ko_KR/zh_TW and Polish; rename runtime symbols, CSS/assets, metadata and storage to Gjallarblog; retain credits/license; add validation, package builder and isolated test setup. No automatic mutation/import of old Evil_Blog storage.

CSV files describe untouched baseline comparisons, not this renamed/restored working tree. Their left side is the named upstream snapshot; right side is the Ainz baseline.

Test core: https://piwigo.org/download/dlcounter.php?code=16.4.0
SHA-256: 72c58a337a1a0639b6ad8dc8f5900a2781fc19091b60ae157eb2ee58c5431c17

Integration follow-up (20 October 2026): replaced the legacy lifecycle with data-preserving PluginMaintain, validated input/CSRF-protected admin mutations, escaped output/editor controls, fixed public routing/pagination/empty views and replaced the legacy RSS dependency. See VALIDATION.md for current evidence; initial comparison CSVs remain historical baseline records.
