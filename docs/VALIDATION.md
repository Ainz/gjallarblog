# Alpha validation — 20 October 2026

Release: **0.1.0-alpha.1**. Six disposable Docker galleries passed the same **44 integration checks and 21 helper checks each** (390 check executions). Evidence is retained under [test-evidence](test-evidence/), with exact machine-readable results in [matrix.json](test-evidence/matrix.json).

| Piwigo | PHP | Database | Result |
|---|---|---|---|
| 15.7.0 | 8.2.34 | MariaDB 11.4.13 | PASS |
| 15.7.0 | 8.3.35 | MariaDB 11.4.13 | PASS |
| 16.4.0 | 8.2.34 | MariaDB 11.4.13 | PASS |
| 16.4.0 | 8.3.35 | MariaDB 11.4.13 | PASS |
| 16.4.0 | 8.4.26 | MariaDB 11.4.13 | PASS |
| 16.4.0 | 8.5.11 | MariaDB 11.4.13 | PASS |

This verifies these exact combinations, not every Piwigo 15/16 patch or arbitrary plugin/theme combination. Piwigo 15 with PHP 8.4/8.5 was not tested or claimed. Use current security-patched core releases; compatibility with an older core does not establish its security support status.

## What was exercised

- Empty/populated home, journal, pagination, single entries and RSS; malformed public routes and missing posts.
- Administrator edit/settings screens; missing and incorrect CSRF tokens; malformed IDs, arrays, title limits and zero page size.
- Ordinary users with valid session tokens denied access to both administration tabs. Guest requests cannot create posts.
- Create/edit/delete; apostrophes, escaped title markup, CJK characters and four-byte Unicode; RSS round trips with forbidden XML controls removed.
- Plugin deactivation, activation, uninstall, reinstall and reactivation preserve posts and settings. Test journals return to empty after cleanup.
- Helper checks for configuration fallback, object deserialization rejection, valid UTF-8 truncation, bounded pagination and invalid UTF-8/array rejection.
- Package metadata, required resources, template references, archive allowlist and 56 runtime PHP syntax checks.

The tests exposed an actual four-byte Unicode insertion failure caused by the core's default utf8 connection. Plugin table queries now scope utf8mb4 to the query and restore the previous connection charset. Core configuration fields reject unsupported four-byte characters with a controlled error rather than a database failure.

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

This is an alpha, not an exhaustive security audit. MySQL, older/newer core patches, other themes, subdirectory/rewrite modes, optional ExtendedDescription integration, complete translations, and legacy migration remain unverified. Trusted administrator HTML is intentionally not sanitized. Full static analysis and broader accessibility/theming work remain before an RC.

Earlier visual evaluation covered Modus home, album, journal and editor screens on the port-8088 gallery. Its banner is site configuration and demonstration artwork, not a plugin theme requirement. User-added evaluation content was excluded from the disposable matrix.
