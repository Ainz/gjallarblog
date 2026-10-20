First tested **Gjallarblog alpha**, derived from Evil_Blog by EvilKant through Ainz/evileblogupdate. Original Git history, attribution and applicable license notices are retained.

Includes journal listing and individual entries, administrator editing, RSS, a standalone Piwigo menu block with configurable label, validated requests, CSRF protection and data-preserving plugin lifecycle. Unicode table queries and XML control handling were corrected during validation.

Tested with MariaDB 11.4.13:

- Piwigo 15.7.0: PHP 8.2.34 and 8.3.35.
- Piwigo 16.4.0: PHP 8.2.34, 8.3.35, 8.4.26 and 8.5.11.
- All six combinations passed 44 integration checks plus 21 helper checks each.

Download **gjallarblog-0.1.0-alpha.1.zip**, verify against **SHA256SUMS.txt**, and extract `gjallarblog/` into your Piwigo `plugins/` directory. GitHub's automatic source archives are not the installable plugin package. Back up your database and evaluate on staging first.

**Known limitations:** experimental alpha; trusted administrator body HTML; incomplete translations and theme coverage; no automatic Evil_Blog migration, export or purge UI; no exhaustive security audit. Four-byte Unicode is supported in posts but rejected in settings stored in the core configuration table. Other database/version combinations remain unverified. Disabling/uninstalling preserves journal data.

See [installation and limitations](https://github.com/Ainz/Gjallarblog/blob/main/README.md), [validation evidence](https://github.com/Ainz/Gjallarblog/blob/main/docs/VALIDATION.md), [changelog](https://github.com/Ainz/Gjallarblog/blob/main/CHANGELOG.md), [licensing](https://github.com/Ainz/Gjallarblog/blob/main/NOTICE.md) and the separate [legacy migration assessment](https://github.com/Ainz/Gjallarblog/blob/main/docs/LEGACY-MIGRATION.md).
