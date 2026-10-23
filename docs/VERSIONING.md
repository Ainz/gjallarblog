# Gjallarblog versioning

Adopted 21 October 2026. Versions are strings, never decimal numbers or strict three-component SemVer.

## Convention

- Numeric base: `Major.Minor`. Major has no padding; minor is an integer displayed with a minimum of two digits. Examples: `0.01`, `0.02`, `0.09`, `0.10`, `0.11`, `1.00`. After `0.99`, `0.100` is valid if a major bump is not warranted. Avoid redundant leading zeros beyond the two-digit minimum.
- Testing stages: `0.03-alpha.1`, `0.03-alpha.2`, `0.03-beta.1`, `0.03-rc.1`; stable: `0.03`. Use lowercase `alpha`, `beta`, `rc`. A sequence number may be omitted when advancing to a new numeric base (for example `0.06-beta`); increment it for a replacement prerelease on the same base. Never overwrite a published tag or asset.
- Every stage sorts before the final version of the same numeric base. A subsequent minor's alpha sorts after the previous minor's final version.
- Git tag: `v0.03-alpha.1`; release title: `Gjallarblog 0.03-alpha.1`; package: `gjallarblog-0.03-alpha.1.zip`; plugin header: `Version: 0.03-alpha.1`. The `v` is only a tag prefix.
- GitHub `prerelease=true` for alpha/beta/rc and `false` for stable. The first stable `1.00` remains a future readiness decision.

## Transition

Keep `v0.1.0-alpha.1` and `v0.1.0-alpha.2`, their original commits, release text, ZIP assets and checksums unchanged. The next release is `0.03-alpha.1`: the third release, and the first under the new convention.

Piwigo treats padding numerically (`0.01 == 0.1`), but missing components matter: `0.01-alpha.1 < 0.1.0-alpha.1`. Restarting at 0.01 would therefore prevent the normal higher-version update detection. `0.03-alpha.1` is greater than both historical releases. Do not retroactively create two-component aliases for them.

## Piwigo handling

Inspected the actual pinned Piwigo 15.7.0 and 16.4.0 core sources used by the compatibility galleries:

- `include/functions.inc.php`: `safe_version_compare` ultimately calls PHP's `version_compare`, with handling for single-letter Piwigo revision names. Numeric padding and full-word prerelease identifiers work with it. Avoid shorthand `a`/`b`, which the wrapper transforms differently.
- `include/functions_plugins.inc.php`: `autoupdate_plugin` parses the header with `[\w.-]+`, compares installed versus filesystem version using `safe_version_compare(..., '<')`, calls the maintenance update hook and records the new version. It preserves the padded string. The header remains on line 3, inside the core's first-lines scan.
- `admin/include/updates.class.php`: available extension revisions are compared with the same comparator; an installed version greater than or equal to the advertised revision clears its pending update.
- `admin/include/plugins.class.php`: PEM compatibility uses exact revision-name strings. Therefore a future PEM revision must use the exact metadata spelling, including padding and prerelease suffix. Revision-date sorting is separate from numeric upgrade comparison.

GitHub releases are not automatically discovered by these core mechanisms. Gjallarblog has no PEM identity yet; manual package replacement is the supported path. GitHub's prerelease flag does not prevent PEM from advertising a revision. Any future PEM channel must intentionally select published compatible revisions and receive its own extension identity.

Piwigo's [publishing guidance](https://github.com/Piwigo/Piwigo/wiki/Publishing-an-extension) recommends core-major/letter names for most extensions. This project follows the owner's two-component convention, which the tested core accepts. PEM publication policy should be reviewed separately before submission; these tests establish runtime compatibility, not catalog acceptance.

Official source references: [Piwigo 16 core updater](https://github.com/Piwigo/Piwigo/blob/16.4.0/include/functions_plugins.inc.php), [Piwigo 16 extension checks](https://github.com/Piwigo/Piwigo/blob/16.4.0/admin/include/updates.class.php), [Piwigo 15 comparator](https://github.com/Piwigo/Piwigo/blob/15.7.0/include/functions.inc.php).

## Release process

The current local candidate is **0.06-beta.1**: metadata `Version: 0.06-beta.1`, planned tag `v0.06-beta.1`, package `gjallarblog-0.06-beta.1.zip`, and GitHub release status `prerelease`. This increments the prerelease counter from the prior local 0.06-beta candidate. The package is local; no tag, GitHub release or Piwigo Extension Manager revision has been created.

1. Update `main.inc.php`, current README/release notes and append the changelog; preserve historical version references.
2. Run package validation. `tools/version.php` validates the canonical version syntax; the builder derives the filename and checksum from it.
3. Test version ordering and core upgrade behavior on disposable Piwigo 15/16 galleries with `tools/test-versioning.php --upgrade` and `GJALLARBLOG_DISPOSABLE=1`. Keep result files under `docs/test-evidence/`.
4. Commit, push and create a new `v<version>` tag at the exact tested commit. `python tools/publish-release.py publish` verifies this tag, metadata, archive contents, checksum and clean working tree before creating the GitHub release. It derives prerelease status from the suffix.
5. Run `python tools/publish-release.py verify` to download published assets again and verify their hashes and metadata. Preserve older releases.

The Piwigo updater can compare the installed version with revisions advertised by PEM. The local package can exercise core version comparison and upgrade handling, but the normal update-notification screen cannot discover it until Gjallarblog has a PEM extension identity and published revision. GitHub prerelease status alone is not queried by Piwigo. See the official [extension publishing guide](https://github.com/Piwigo/Piwigo/wiki/Publishing-an-extension).

The previous `0.05-beta.1` candidate passed 43 core versioning/upgrade checks on each of Piwigo 15.7.0 / PHP 8.3.36 and Piwigo 16.4.0 / PHP 8.4.27. The `0.06-beta` run passed 51 core comparison/autoupdate checks on each of those Piwigo/PHP combinations. The `0.06-beta.1` follow-up adds an explicit comparison and upgrade from `0.06-beta`; the refreshed evidence is in `test-evidence/a15-versioning.txt` and `a16-versioning.txt`.
