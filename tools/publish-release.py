"""Publish or verify a release derived from main.inc.php; never replace old releases.

Prerequisites: clean committed checkout, pushed v<version> tag, built package.
Uses the configured Git credential helper without printing authentication data.
"""
import argparse, hashlib, json, pathlib, subprocess, urllib.request, urllib.parse, zipfile

ROOT = pathlib.Path(__file__).resolve().parents[1]
REPO = 'Ainz/gjallarblog'

def git(*args):
    return subprocess.check_output(['git', *args], cwd=ROOT, text=True).strip()

def validate_package(version):
    name = f'gjallarblog-{version}.zip'
    archive = ROOT/'dist'/name
    with zipfile.ZipFile(archive) as package:
        for item in package.infolist():
            if item.is_dir():
                continue
            relative = pathlib.PurePosixPath(item.filename)
            if relative.parts[0] != 'gjallarblog' or any(p in relative.parts for p in ['..','.git','.dev','dev','tools','dist']):
                raise RuntimeError('Unexpected archive path: '+item.filename)
            repository_docs = ['LICENSE','NOTICE.md','README.md','CHANGELOG.md']
            source = ROOT if relative.parts[1] in repository_docs else ROOT/'plugin'
            source = source.joinpath(*relative.parts[1:])
            if package.read(item) != source.read_bytes():
                raise RuntimeError('Stale archive file: '+item.filename)
        metadata = package.read('gjallarblog/main.inc.php').decode('utf-8')
        if f'Version: {version}' not in metadata.splitlines():
            raise RuntimeError('Archive metadata disagrees with version')
    checksum = hashlib.sha256(archive.read_bytes()).hexdigest()
    if (ROOT/'dist/SHA256SUMS.txt').read_text(encoding='ascii').strip() != f'{checksum}  {name}':
        raise RuntimeError('Checksum file disagrees with archive')
    return name

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('action', choices=['publish','verify'])
    args = parser.parse_args()
    version = subprocess.check_output(['php', str(ROOT/'tools/version.php')], text=True).strip()
    name = validate_package(version)
    if git('status','--porcelain'):
        raise RuntimeError('Commit all changes before release')
    tag = 'v'+version
    commit = git('rev-parse','HEAD')
    if git('rev-parse',tag+'^{commit}') != commit:
        raise RuntimeError('Version tag does not identify the current commit')
    credential = subprocess.run(['git','credential','fill'],input='protocol=https\nhost=github.com\n\n',text=True,capture_output=True,cwd=ROOT,check=True)
    token = dict(line.split('=',1) for line in credential.stdout.splitlines() if '=' in line)['password']

    def api(method,path,body=None,raw=None,mime='application/json'):
        url = path if path.startswith('https://') else 'https://api.github.com'+path
        if urllib.parse.urlparse(url).hostname not in ['api.github.com','uploads.github.com']:
            raise RuntimeError('Unexpected GitHub API host')
        data = raw if raw is not None else (json.dumps(body).encode() if body is not None else None)
        req = urllib.request.Request(url,data=data,method=method,headers={'Authorization':'Bearer '+token,'Accept':'application/vnd.github+json','Content-Type':mime,'X-GitHub-Api-Version':'2022-11-28'})
        with urllib.request.urlopen(req,timeout=90) as response:
            return json.load(response)

    prefix = '/repos/'+REPO
    if api('GET',prefix+'/commits/'+tag)['sha'] != commit:
        raise RuntimeError('Pushed tag does not identify the tested commit')
    prerelease = '-' in version
    if args.action == 'publish':
        # GitHub rejects an existing release for the same tag. No overwrite path.
        release = api('POST',prefix+'/releases',{'tag_name':tag,'name':'Gjallarblog '+version,'body':(ROOT/'docs/RELEASE-NOTES.md').read_text(encoding='utf-8'),'draft':True,'prerelease':prerelease})
        for asset in [name,'SHA256SUMS.txt']:
            api('POST',release['upload_url'].split('{')[0]+'?name='+urllib.parse.quote(asset),raw=(ROOT/'dist'/asset).read_bytes(),mime='application/zip' if asset.endswith('.zip') else 'text/plain')
        release = api('PATCH',prefix+'/releases/'+str(release['id']),{'draft':False})
    release = api('GET',prefix+'/releases/tags/'+tag)
    if release['draft'] or release['prerelease'] != prerelease or release['name'] != 'Gjallarblog '+version:
        raise RuntimeError('GitHub release status/name disagrees with metadata')
    if {a['name'] for a in release['assets']} != {name,'SHA256SUMS.txt'}:
        raise RuntimeError('Unexpected release assets')
    for asset in release['assets']:
        # Public download: do not forward API credentials to download hosts.
        with urllib.request.urlopen(asset['browser_download_url'],timeout=90) as response:
            if response.read() != (ROOT/'dist'/asset['name']).read_bytes():
                raise RuntimeError('Published asset differs: '+asset['name'])
    print(json.dumps({'version':version,'tag':tag,'commit':commit,'prerelease':prerelease,'release':release['html_url'],'assets_verified':True}))

if __name__ == '__main__':
    main()
