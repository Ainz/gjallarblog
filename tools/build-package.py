#!/usr/bin/env python3
"""Build a Piwigo-ready Gjallarblog ZIP from plugin/ into dist/."""

import argparse
import hashlib
import pathlib
import stat
import subprocess
import tempfile
import zipfile

ROOT = pathlib.Path(__file__).resolve().parents[1]
PLUGIN = ROOT / 'plugin'
DIST = ROOT / 'dist'
REPOSITORY_FILES = ('LICENSE', 'NOTICE.md', 'README.md', 'CHANGELOG.md')


def run(*command):
    return subprocess.run(command, cwd=ROOT, check=True, text=True, capture_output=True).stdout.strip()


def release_files():
    files = []
    for path in PLUGIN.rglob('*'):
        if path.is_symlink():
            raise RuntimeError(f'Symlinks are not allowed in the plugin package: {path.relative_to(ROOT)}')
        if path.is_file() and path.relative_to(PLUGIN).as_posix() != 'rss.png':
            files.append((path, pathlib.PurePosixPath('gjallarblog', path.relative_to(PLUGIN).as_posix())))
    for name in REPOSITORY_FILES:
        path = ROOT / name
        if not path.is_file():
            raise RuntimeError(f'Missing repository distribution file: {name}')
        files.append((path, pathlib.PurePosixPath('gjallarblog', name)))
    return sorted(files, key=lambda item: item[1].as_posix())


def build(force=False):
    run('php', str(ROOT / 'tools' / 'check-package.php'))
    version = run('php', str(ROOT / 'tools' / 'version.php'))
    archive = DIST / f'gjallarblog-{version}.zip'
    checksum_file = DIST / 'SHA256SUMS.txt'
    if not force and (archive.exists() or checksum_file.exists()):
        raise FileExistsError('A package or checksum already exists in dist/; pass --force to replace it.')

    DIST.mkdir(exist_ok=True)
    files = release_files()
    if not any(destination.as_posix() == 'gjallarblog/main.inc.php' for _, destination in files):
        raise RuntimeError('plugin/main.inc.php is missing')

    temporary = None
    try:
        with tempfile.NamedTemporaryFile(prefix='.gjallarblog-', suffix='.zip', dir=DIST, delete=False) as handle:
            temporary = pathlib.Path(handle.name)
        with zipfile.ZipFile(temporary, 'w', compression=zipfile.ZIP_DEFLATED, compresslevel=9) as package:
            for source, destination in files:
                info = zipfile.ZipInfo(destination.as_posix(), date_time=(1980, 1, 1, 0, 0, 0))
                info.compress_type = zipfile.ZIP_DEFLATED
                info.external_attr = (stat.S_IFREG | 0o644) << 16
                package.writestr(info, source.read_bytes(), compress_type=zipfile.ZIP_DEFLATED, compresslevel=9)

        with zipfile.ZipFile(temporary) as package:
            names = package.namelist()
            if len(names) != len(set(names)) or any(name.startswith('gjallarblog/plugin/') for name in names):
                raise RuntimeError('Package paths are duplicated or incorrectly nested')
            if 'gjallarblog/main.inc.php' not in names or 'gjallarblog/template/blog.tpl' not in names:
                raise RuntimeError('Package is missing its Piwigo entry point or template directory')
            if any('/test-evidence/' in name or name.endswith('/rss.png') for name in names):
                raise RuntimeError('Local test evidence or the unused RSS image entered the release package')

        digest = hashlib.sha256(temporary.read_bytes()).hexdigest()
        DIST.mkdir(exist_ok=True)
        temporary.replace(archive)
        checksum_tmp = checksum_file.with_suffix('.txt.tmp')
        checksum_tmp.write_text(f'{digest}  {archive.name}\n', encoding='ascii', newline='\n')
        checksum_tmp.replace(checksum_file)
        return archive, digest, len(files)
    finally:
        if temporary is not None and temporary.exists():
            temporary.unlink()


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--force', action='store_true', help='replace an existing local package and checksum')
    args = parser.parse_args()
    archive, digest, count = build(args.force)
    print(f'PASS: {count} files packaged under gjallarblog/; SHA-256 {digest}')
    print(archive.relative_to(ROOT))


if __name__ == '__main__':
    main()
