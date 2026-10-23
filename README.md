# Gjallarblog

A journal plugin for Piwigo, maintained by Ainz and derived from **Evil_Blog by EvilKant**, with contributions from Piwigo translators. The original Git history is preserved.

**0.06-beta.1 is a local beta prerelease candidate; the latest published release remains 0.04-alpha.1.** Back up your database and test on a staging gallery before deployment. It is not a release candidate or a claim of a complete security audit.

## Installation

1. For normal installation, download the latest published package from [Releases](https://github.com/Ainz/gjallarblog/releases). The 0.06-beta.1 candidate is available in the local `dist/` folder for evaluation.
2. Extract its single `gjallarblog/` directory into your gallery's `plugins/` directory. Use this release ZIP rather than GitHub's source archive.
3. In Piwigo Administration → Plugins, install and activate **Gjallarblog**.
4. Open the plugin settings and configure the journal name, description, page size and placement on gallery home/album pages.
5. Create entries using the plugin's Entries tab. The journal is available at `?/blog`. RSS belongs to Piwigo; the plugin has no separate feed link.

The Blog menu block uses Piwigo's menu manager. Reorder or hide it in Administration → Configuration → Menus. One plain-text Journal name controls the menu item, journal heading and entry return links; the active theme styles those labels. Use Settings → Site navigation to show or hide the link, and the menu manager to position it. Removal options have a separate tab.

## Versioning

Versions use **Major.Minor**, with the minor displayed using at least two digits: `0.03`, `0.09`, `0.10`, eventually `1.00`. Prereleases append `-alpha.N`, `-beta.N` or `-rc.N` and are marked as prereleases on GitHub. The metadata version, tag (`v` prefix), package filename and release title share the same version string.

The historical `0.1.0-alpha.1` and `0.1.0-alpha.2` releases remain unchanged. The convention was introduced in **0.03-alpha.1**; the current local candidate is **0.06-beta.1**, following **0.06-beta**. See [versioning and upgrade verification](docs/VERSIONING.md).

## Compatibility

Tested: **Piwigo 15.7.0 with PHP 8.2.35/8.3.36**, and **Piwigo 16.4.0 with PHP 8.2.35/8.3.36/8.4.27/8.5.12**. Beta.1 passed 51 integration, 30 helper and 84 usability/security checks in each combination; version comparison and upgrade checks passed on Piwigo 15 and 16. PHP requires `mysqli` and `mbstring`, in addition to the requirements of the selected Piwigo version. See [validation](docs/VALIDATION.md) for per-combination results.

The beta targets Piwigo 15 and 16; only exact combinations with passing results in [validation](docs/VALIDATION.md) are claimed as tested. Other combinations remain unverified. MariaDB 11.4 is the test database; MySQL and other database versions are unverified.

PHP 8.2–8.5 compatibility is a development target where the underlying Piwigo release supports it, not a blanket support claim. Use currently maintained PHP and Piwigo releases. See [PHP support](https://www.php.net/supported-versions.php).

## Security and data behavior

Only gallery administrators can manage entries and settings. Changes require Piwigo CSRF tokens. Titles and form values are escaped; identifiers, pagination, UTF-8 and input sizes are validated.

**Post bodies are filtered through an HTML allowlist on save, preview and public display.** HTML Purifier removes scripts, event attributes, inline styles, active embeds and unsupported URL schemes. Existing stored bodies are not rewritten on upgrade; their displayed output is filtered. Editing and saving an older entry persists the filtered content. Do not grant administrator access to untrusted authors. There is no public submission, dedicated author role, private-post permission model, or draft workflow. Published journal posts are public when the gallery permits public access; album restrictions do not make journal entries private.

Disabling or uninstalling the plugin preserves its posts, settings and image files. Reinstalling reconnects to the post and media records. Back up the `<prefix>gjallarblog` and `<prefix>gjallarblog_media` tables, `gjallarblog` configuration entry and `_data/gjallarblog/media/` directory. Settings → Plugin removal shows the current entry count and offers verified folder removal. Keeping data is the default. Only a webmaster can remove the plugin through this screen. Erasure additionally requires typing `Gjallarblog` and checking confirmation; data is erased only after successful folder removal. Piwigo’s plugin-manager delete action preserves journal data for reinstalling. Use Settings → Plugin removal for the counted choice between keeping it or erasing it. Read-only installations must be removed by the server administrator.

## Writing entries

Titles accept **255 characters** and content accepts **10,000 characters of HTML source**, including tags. The editor shows live counters and the server enforces both limits. Characters mean Unicode code points; a combined emoji or accented character can contain more than one. Line endings are normalized. Text is retained when ordinary validation fails.

Small buttons insert paragraphs, bold, italic, headings, lists, quotes and links. The help panel shows examples for headings, rules, quotations and code. **Preview** shows filtered content without saving it; **Publish entry** and **Save changes** make entries public immediately. Drafts are not available. This is an HTML editor with formatting aids, not a full visual editor. A browser warning helps prevent accidental navigation away from changed forms.

Successful saves return to the saved entry on its actual list page. Deletion has a separate confirmation checkbox and names the entry in the browser confirmation.

Entries support a gallery-timezone publication date for historical republishing; future dates are rejected. Images can be pasted from the clipboard or selected as files. Supported PNG, JPEG and WebP uploads are bounded, decoded and re-encoded to remove embedded metadata, then stored separately from post markup. Images default to a centered block with no text wrap; authors can choose left/right blocks, inline placement, size, alternative text and captions. Existing image placement can be edited from the entry form. Media is served through a Piwigo route. This beta requires GD with WebP support for uploads.

## Upgrading from alpha.1

Back up the database and existing plugin folder, replace `plugins/gjallarblog/` with the release package, and reload the administration page. Posts and site branding are preserved. Review older content that uses scripts, embeds or inline styles because those elements are now removed from displayed output. Existing entries above the new 10,000-character limit remain readable, but must be shortened before they can be saved again. Settings change to JSON on their next save or plugin reinstall.

## Known limitations

- No automatic Evil_Blog migration. Old storage is neither changed nor removed. See [migration assessment](docs/LEGACY-MIGRATION.md).
- Existing development tables are not automatically converted between character sets; install into a fresh Gjallarblog table for beta evaluation.
- New administration labels are English; inherited translations are retained but translation coverage is incomplete.
- Theme evaluation focuses on the bundled Modus theme. Journal styles inherit theme colors and use scoped layout rules; custom themes may need local CSS adjustments and have not all been tested.
- The date selector offers localized, ISO (`Y-m-d`) and day/month/year (`d/m/Y`) formats. All use the gallery timezone; the localized month name follows Piwigo's language.
- The former `?/blog/feed` redirects to Piwigo’s core `feed.php`. Journal entries are not added to the core photo feed. Piwigo’s standard feed navigation and access rules apply; hiding its menu link alone does not disable its feed endpoint.
- ExtendedDescription integration is optional and guarded, but has not been tested as part of the beta matrix. The old FCKEditor dependency is not required.
- Settings are stored as ASCII-escaped JSON to preserve emoji in the core configuration table. Legacy serialized settings remain readable. Downgrading to alpha.1 after saving alpha.2 settings requires restoring the backed-up configuration entry.
- No independent penetration test, full static-analysis certification, or guarantee covering all third-party plugins.

## Development and packaging

On the original Windows/WSL development environment, `tools/Start-TestGallery.ps1` uses the shared Failover WSL lease helper and starts a localhost-only evaluation gallery on port 8088. Its location can be overridden with `GJALLARBLOG_WSL_HELPER`. It never shuts down WSL globally.

`tools/start-alpha.sh` creates separate disposable Docker galleries for the Piwigo 15/16 matrix on ports 8089-8094. It currently assumes this project's Windows location at `/mnt/d/Development/gjallarblog`. See [validation](docs/VALIDATION.md) for the exact commands. Credentials and runtime data are ignored under `.dev/`; do not commit them.

Run `php tools/check-package.php`, then `tools/Build-DevPackage.ps1` from PowerShell to produce the installable ZIP under `dist/`. The ZIP excludes development tooling, generated gallery assets and secrets. Repository source includes development tooling; it is not the installable package.

See [changelog](CHANGELOG.md), [attribution/licensing](NOTICE.md), and [source provenance](docs/UPSTREAM.md). Report defects through [GitHub issues](https://github.com/Ainz/gjallarblog/issues); report sensitive security details privately to the maintainer before posting publicly.
