# Gjallarblog

A journal plugin for Piwigo, maintained by Ainz and derived from **Evil_Blog by EvilKant**, with contributions from Piwigo translators. The original Git history is preserved.

**0.1.0-alpha.2 is an experimental prerelease.** Back up your database and test on a staging gallery before deployment. It is not a release candidate or a claim of a complete security audit.

## Installation

1. Download `gjallarblog-0.1.0-alpha.2.zip` from [Releases](https://github.com/Ainz/gjallarblog/releases).
2. Extract its single `gjallarblog/` directory into your gallery's `plugins/` directory. Use this release ZIP rather than GitHub's source archive.
3. In Piwigo Administration → Plugins, install and activate **Gjallarblog**.
4. Open the plugin settings and configure the journal name, description, menu label, page size and placement on gallery home/album pages.
5. Create entries using the plugin's Entries tab. The journal is available at `?/blog`; RSS is at `?/blog/feed`.

The Blog menu block uses Piwigo's menu manager. Reorder or hide it in Administration → Configuration → Menus. Presentation depends on the active theme. Renaming the journal and renaming its menu label are separate settings. A blank menu label falls back to the journal title. Use the menu manager to hide the block. The Settings tab links directly to it.

## Compatibility

Tested: **Piwigo 15.7.0 with PHP 8.2.34/8.3.35**, and **Piwigo 16.4.0 with PHP 8.2.34/8.3.35/8.4.26/8.5.11**. Each combination passed 44 integration, 21 helper and 56 usability/security checks on MariaDB 11.4.13.

PHP requires `mysqli` and `mbstring`, in addition to the requirements of the selected Piwigo version. The alpha targets Piwigo 15 and 16; only exact combinations with passing results in [validation](docs/VALIDATION.md) are claimed as tested. Other combinations remain unverified. MariaDB 11.4 is the test database; MySQL and other database versions are unverified.

PHP 8.2–8.5 compatibility is a development target where the underlying Piwigo release supports it, not a blanket support claim. Use currently maintained PHP and Piwigo releases. See [PHP support](https://www.php.net/supported-versions.php).

## Security and data behavior

Only gallery administrators can manage entries and settings. Changes require Piwigo CSRF tokens. Titles and form values are escaped; identifiers, pagination, UTF-8 and input sizes are validated.

**Post bodies are filtered through an HTML allowlist on save, preview, public display and RSS.** HTML Purifier removes scripts, event attributes, inline styles, active embeds and unsupported URL schemes. Existing stored bodies are not rewritten on upgrade; their displayed output is filtered. Editing and saving an older entry persists the filtered content. Do not grant administrator access to untrusted authors. There is no public submission, dedicated author role, private-post permission model, or draft workflow. Published journal posts and RSS are public when the gallery permits public access; album restrictions do not make journal entries private.

Disabling or uninstalling the plugin preserves its posts and settings. Reinstalling reconnects to them. Back up the `<prefix>gjallarblog` table and `gjallarblog` configuration entry. This alpha has no permanent-purge interface.

## Writing entries

Titles accept **255 characters** and content accepts **10,000 characters of HTML source**, including tags. The editor shows live counters and the server enforces both limits. Characters mean Unicode code points; a combined emoji or accented character can contain more than one. Line endings are normalized. Text is retained when ordinary validation fails.

Small buttons insert paragraphs, bold, italic, headings, lists, quotes and links. **Preview** renders filtered content without publishing; **Publish entry** and **Save changes** make it public immediately. This is an HTML editor with formatting aids, not a full visual editor. There is no draft store. A browser warning helps prevent accidental navigation away from changed forms.

Successful saves return to the saved entry on its actual list page. Deletion has a separate confirmation checkbox and names the entry in the browser confirmation.

## Upgrading from alpha.1

Back up the database and existing plugin folder, replace `plugins/gjallarblog/` with the release package, and reload the administration page. Posts and site branding are preserved. Review older content that uses scripts, embeds or inline styles because those elements are now removed from displayed output. Existing entries above the new 10,000-character limit remain readable, but must be shortened before they can be saved again. Settings change to JSON on their next save or plugin reinstall.

## Known limitations

- No automatic Evil_Blog migration. Old storage is neither changed nor removed. See [migration assessment](docs/LEGACY-MIGRATION.md).
- Existing development tables are not automatically converted between character sets; install into a fresh Gjallarblog table for alpha evaluation.
- New administration labels are English; inherited translations are retained but translation coverage is incomplete.
- Theme evaluation focuses on the bundled Modus theme. Per-theme template overrides and broader theme validation remain future work.
- Default `j F Y` dates use Piwigo localization. Custom PHP date formats use the server timezone and may contain English month/day names.
- RSS includes at most the latest 50 posts. Relative URLs inside authored HTML are not rewritten for feed readers.
- ExtendedDescription integration is optional and guarded, but has not been tested as part of the alpha matrix. The old FCKEditor dependency is not required.
- Settings are stored as ASCII-escaped JSON to preserve emoji in the core configuration table. Legacy serialized settings remain readable. Downgrading to alpha.1 after saving alpha.2 settings requires restoring the backed-up configuration entry.
- No independent penetration test, full static-analysis certification, or guarantee covering all third-party plugins.

## Development and packaging

On the original Windows/WSL development environment, `tools/Start-TestGallery.ps1` uses the shared Failover WSL lease helper and starts a localhost-only evaluation gallery on port 8088. Its location can be overridden with `GJALLARBLOG_WSL_HELPER`. It never shuts down WSL globally.

`tools/start-alpha.sh` creates separate disposable Docker galleries for the Piwigo 15/16 matrix on ports 8089-8094. It currently assumes this project's Windows location at `/mnt/d/Development/gjallarblog`. See [validation](docs/VALIDATION.md) for the exact commands. Credentials and runtime data are ignored under `.dev/`; do not commit them.

Run `php tools/check-package.php`, then `tools/Build-DevPackage.ps1` from PowerShell to produce the installable ZIP under `dist/`. The ZIP excludes development tooling, generated gallery assets and secrets. Repository source includes development tooling; it is not the installable package.

See [changelog](CHANGELOG.md), [attribution/licensing](NOTICE.md), and [source provenance](docs/UPSTREAM.md). Report defects through [GitHub issues](https://github.com/Ainz/gjallarblog/issues); report sensitive security details privately to the maintainer before posting publicly.
