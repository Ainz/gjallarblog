"""Add ten identified fixtures to the explicitly authorized local evaluation gallery.

Never edits or deletes existing entries. A manifest allows safe resumption.
Run with the project path; only localhost port 8088 is used.
"""
import pathlib,sys,json,random,urllib.request,urllib.parse,http.cookiejar
root=pathlib.Path(sys.argv[1]).resolve()
cred=json.loads((root/'.dev/test-credentials.json').read_text(encoding='utf-8-sig'))
manifest=root/'.dev/length-fixtures.json'
rows=json.loads(manifest.read_text(encoding="utf-8")) if manifest.exists() else []
client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
base='http://127.0.0.1:8088/'
def req(path,data=None):
    response=client.open(urllib.request.Request(base+path,data=urllib.parse.urlencode(data).encode() if data else None),timeout=45)
    return response.read().decode('utf-8'),response.geturl()
def api(data):
    result=json.loads(req('ws.php?format=json',data)[0]);assert result['stat']=='ok';return result['result']
api({'method':'pwg.session.login','username':cred['username'],'password':cred['password']})
token=api({'method':'pwg.session.getStatus'})['pwg_token']
rng=random.Random(20261021)
sizes=[rng.randint(180,950) if i%2==0 else rng.randint(5500,9800) for i in range(10)]
sizes[-1]=10000
names=['Morning at the harbour','Across the northern hills','A brief pause in the rain','The long way home','Light on the water','Field notes from a changing coast','A small observation','Walking through the seasons','Unicode: blåbär, 北, and 🌄','At the 10,000-character boundary']
lorem='Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vitae lacus a nibh malesuada tincidunt. Integer placerat, tellus vel cursus volutpat, neque justo facilisis risus, at vulputate sapien libero vitae purus. '
for i,size in enumerate(sizes):
    if any(row['fixture']==i+1 for row in rows):continue
    title=f'Test {i+1:02}: {names[i]}'
    if i==7:title=(title+' — '+('A long title for testing wrapping and readability. '*8))[:255]
    body=f'<p>Evaluation fixture {i+1:02}. This entry tests {size:,} characters of HTML source.</p>'
    while len(body)+357<size:body+='<p>'+lorem+'The gallery journal should remain comfortable to read on a narrow screen.</p>'
    remaining=size-len(body)-7
    body+='<p>'+(lorem*((remaining//len(lorem))+1))[:remaining]+'</p>'
    assert len(body)==size
    html,url=req('admin.php?page=plugin-gjallarblog-manage',{'pwg_token':token,'submit_post':'1','post_header':title,'post_textarea':body})
    ident=int(urllib.parse.parse_qs(urllib.parse.urlsplit(url).query)['edit'][0])
    rows.append({'fixture':i+1,'id':ident,'title_characters':len(title),'body_characters':size,'title':title})
    manifest.write_text(json.dumps(rows,ensure_ascii=False,indent=2),encoding='utf-8')
    print(f'Added test entry {i+1:02}: {size} body characters, {len(title)} title characters',flush=True)
print(f'{len(rows)} additive fixtures available at {base}?/blog')
