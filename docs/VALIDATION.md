# Beta validation — 22 October 2026

The 0.06-beta.1 candidate updates menu markup, entry-number visibility, Restore guidance, recent-entry limits and date-format selection. Each matrix combination passed 51 integration, 30 helper and 84 usability/security checks. Piwigo 15.7.0 and 16.4.0 each passed 57 version comparison/autoupdate checks, including the upgrade from 0.06-beta. A disposable removal lifecycle check confirmed Restore and reinstall preserve posts, settings and images, while explicit erase removes them after the plugin folder is gone. Earlier tagged-release evidence remains in its original commits.

| Piwigo | PHP | Database | Result |
|---|---|---|---|
| 15.7.0 | 8.2.35 | MariaDB 11.4.13 | PASS |
| 15.7.0 | 8.3.36 | MariaDB 11.4.13 | PASS |
| 16.4.0 | 8.2.35 | MariaDB 11.4.13 | PASS |
| 16.4.0 | 8.3.36 | MariaDB 11.4.13 | PASS |
| 16.4.0 | 8.4.27 | MariaDB 11.4.13 | PASS |
| 16.4.0 | 8.5.12 | MariaDB 11.4.13 | PASS |

This verifies these exact combinations, not every Piwigo 15/16 patch or arbitrary plugin/theme combination. Piwigo 15 with PHP 8.4/8.5 was not tested or claimed. Use current security-patched core releases; compatibility with an older core does not establish its security support status.

## What was exercised

- Empty/populated home, journal, pagination, single entries, the retired feed redirect and malformed public routes/missing posts.
- Administrator edit/settings screens; missing and incorrect CSRF tokens; malformed IDs, arrays, title limits and zero page size.
- Ordinary users with valid session tokens denied access to both administration tabs. Guest requests cannot create posts.
- Create/edit/delete; apostrophes, escaped title markup, CJK characters and four-byte Unicode; journal settings, placement choices and feed-route retirement.
- Plugin deactivation, activation, uninstall, reinstall and reactivation preserve posts and settings. Removal-screen checks reject missing confirmation and read-only paths.
- On a separate writable Piwigo 16.4 gallery, the removal screen displayed the live entry count, removed the actual plugin directory, preserved and reloaded entries/settings when “keep” was selected, and erased them only after the typed confirmation. A fresh activation after erasure returned an empty journal. The temporary gallery and its database volume were deleted after the check.
- Helper checks for configuration fallback, object deserialization rejection, valid UTF-8 truncation, bounded pagination and invalid UTF-8/array rejection.
- Package metadata, required resources, template references, archive allowlist and 56 runtime PHP syntax checks.

The tests exposed an actual four-byte Unicode insertion failure caused by the core's default utf8 connection. Plugin table queries now scope utf8mb4 to the query and restore the previous connection charset. Alpha.2 stores configuration as ASCII-escaped JSON, with SQL escaping through the core callback, so four-byte characters, quotes and backslashes round-trip safely. The insert ID is captured before charset restoration, which otherwise resets it.

## Reproduction

On the original Windows/WSL2 development machine, with Docker Engine in Debian:

```powershell
./tools/Run-AlphaMatrix.ps1
# Rerun existing disposable instances without rebuilding:
./tools/Run-AlphaMatrix.ps1 -SkipBuild
```

The runner uses the shared Failover WSL lease helper, builds the installable plugin package, starts six isolated projects on localhost ports 8089–8094, installs Piwigo, disables the core empty-gallery welcome screen in those test instances, and runs the suites. It does not change the evaluation gallery on port 8088 or shut down WSL globally. The suite only permits the disposable test ports, creates its own entries/users, and cleans them up.

Current tooling assumes Windows source `D:\Development\gjallarblog` and Debian mount `/mnt/d/Development/gjallarblog`. Adjust tooling paths before use elsewhere. Runtime and generated credentials live under ignored `.dev/` and `/var/lib/gjallarblog-alpha`; no credentials are included in evidence or release assets.

Official core downloads are pinned by SHA-256:

- Piwigo 15.7.0: `41b899cc2d8954bc3024c55b3476ced9104e530d328e6b647f3051f65744d6b6`
- Piwigo 16.4.0: `72c58a337a1a0639b6ad8dc8f5900a2781fc19091b60ae157eb2ee58c5431c17`

## Limits

This beta candidate is not an exhaustive security audit. MySQL, older/newer core patches, other themes, subdirectory/rewrite modes, optional ExtendedDescription integration, complete translations, and legacy migration remain unverified. Entry HTML is now allowlist-filtered on every output path and when saved; this does not establish immunity to every future sanitizer or integration defect. Broader static analysis and accessibility/theming work remain before an RC.

Earlier visual evaluation covered Modus home, album, journal and editor screens on the port-8088 gallery. Its banner is site configuration and demonstration artwork, not a plugin theme requirement. User-added evaluation content was excluded from the disposable matrix. The beta image controls and historical-date field are available in the local editor for hands-on review; the authenticated browser view has not yet been visually reviewed in this beta pass.

## Alpha.2 usability and input checks

The additional HTTP suite (`tools/test-usability.py PORT FIXTURE`) runs only on disposable ports 8089–8094. The folder-removal runner (`tools/Test-Removal.ps1`) creates a uniquely named temporary database/gallery on port 8095 and removes those temporary Docker resources when it finishes. It checks quoted/Unicode settings, every settings field's array rejection, checkbox values, malformed title/body/action fields, invalid UTF-8/control characters, title sanitization and character boundaries, 9,998/9,999/10,000/10,001-character body boundaries, retained errors, HTML sanitation, sandboxed preview without publication, saved-entry pagination, one-time notices, confirmed deletion, RSS, theme-native menu markup, date-format choices, five-entry limits, and entry navigation. It cleans up its own posts. The matrix runner calls it for each fixture; Python 3 is required (`-PythonExecutable` can select its full path). The earlier 0.06-beta run recorded 77 checks; beta.1 records 84 per tested gallery.

The existing local gallery was backed up before deployment. Its original seven posts and banner fingerprints were unchanged by the code update. Ten additive test entries alternate short and long bodies (501–10,000 source characters), including a 255-character title; test data is not packaged.

Earlier alpha browser checks covered Modus public pages and the plugin editor at 320, 390, 768 and 1280 CSS-pixel viewport widths, plus light and dark administration. Beta automated coverage verifies the editor controls and endpoints; the new editor layout still needs hands-on review after signing in. This is viewport reflow testing, not a claim of separate 400% browser-zoom or screen-reader validation. Full accessibility certification and an additional public theme remain outside this beta's verified coverage.

## 0.06-beta settings, navigation and title follow-up

The 0.06-beta matrix passed six Piwigo/PHP combinations at 51 integration, 28 helper and 77 usability/security checks each. This historical run covered plain-text titles, one shared journal name, button-style shortcuts, the separate removal tab, and previous/next entry links. The beta.1 results above extend it.

## 0.06-beta.1 follow-up

The replacement candidate was retested for native-theme menu markup, absence of public database IDs, Restore/removal descriptions, a strict five-entry limit, placement guidance against Piwigo's template slots, and three safe date-format choices. Current compatibility and lifecycle evidence is summarized at the top and in the refreshed evidence files.

## 0.05-beta.1 image and date follow-up

All six Piwigo/PHP matrix combinations passed 51 integration, 27 helper and 70 usability/security checks. This includes historical date parsing, invalid/future date rejection, PNG upload validation, active SVG rejection, media delivery, caption and placement sanitization, and CSRF rejection. Piwigo 15.7.0/PHP 8.3.36 and Piwigo 16.4.0/PHP 8.4.27 each passed 43 version comparison/autoupdate checks, including migration from both published 0.1.0 alpha versions and post/settings preservation. The disposable Piwigo 16.4.0 removal test passed with a real uploaded image: keep/reinstall preserved and served it; confirmed erasure removed the plugin folder and media directory and left a fresh journal empty.

## 0.04-alpha.1 settings, feed and removal follow-up

The settings, placement and retired-feed changes passed the updated 51 integration, 21 helper and 56 usability/security checks on Piwigo 15.7.0/PHP 8.3.35 and Piwigo 16.4.0/PHP 8.4.26. The writable removal flow passed on Piwigo 16.4.0/PHP 8.4.26, including folder absence, retained reinstall data and separately confirmed erasure. The standard compatibility matrix checks read-only removal refusal.

## 0.03-alpha.1 versioning follow-up

The runtime change from 0.1.0-alpha.2 is its metadata version only. Version ordering and actual core autoupdate were exercised separately on Piwigo 15.7.0 and 16.4.0, including both legacy source versions, repeated checks and preservation of posts/settings. See VERSIONING.md and the versioning evidence files. The six-combination application matrix above is the newly tested 0.06-beta.1 candidate.

Verification result: **39 core versioning/upgrade checks passed on each of Piwigo 15.7.0 / PHP 8.3.35 and Piwigo 16.4.0 / PHP 8.4.26 (78 total)**. Both historical release transitions preserved a real post fixture and Unicode/quoted settings. The release-format validator separately passed 22 valid/invalid syntax cases.
