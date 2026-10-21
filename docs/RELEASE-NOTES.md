Gjallarblog **0.03-alpha.1** adopts the requested **Major.Minor** convention. The minor is displayed with at least two digits; alpha/beta/rc status stays explicit in both the identifier and GitHub prerelease flag.

This is a versioning/tooling release with the journal and editor functionality from **0.1.0-alpha.2**. Both historical releases and their tags/assets remain intact. Do not rename an installed or published old version to 0.01: Piwigo would consider that a downgrade. `0.03-alpha.1` compares newer than both published versions.

Download **gjallarblog-0.03-alpha.1.zip**, verify **SHA256SUMS.txt**, back up your gallery and replace `plugins/gjallarblog/`. Piwigo detects the higher filesystem version and updates its stored plugin version through the existing maintenance hook. Posts and normalized settings are preserved.

Version ordering and actual upgrade handling passed **78 focused checks** across Piwigo 15.7.0 and 16.4.0. The release-format validator passed **22 syntax cases**. The unchanged application baseline previously passed 726 integration/helper/usability checks across six Piwigo/PHP combinations. See the versioning report for the additional focused evidence.

This remains an experimental alpha. GitHub prerelease status does not create a Piwigo Extensions Manager update channel; this fork has no PEM extension identity yet. No release was submitted under the old Evil_Blog identity.

See [versioning](https://github.com/Ainz/gjallarblog/blob/main/docs/VERSIONING.md), [installation and limitations](https://github.com/Ainz/gjallarblog/blob/main/README.md), [validation](https://github.com/Ainz/gjallarblog/blob/main/docs/VALIDATION.md), and [changelog](https://github.com/Ainz/gjallarblog/blob/main/CHANGELOG.md).
