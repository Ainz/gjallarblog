"""HTTP regression checks; writes only to the disposable alpha galleries."""
import urllib.request, urllib.parse, urllib.error, http.cookiejar, json, pathlib, sys, re
sys.stdout.reconfigure(encoding='utf-8')
root=pathlib.Path(__file__).resolve().parents[1]
port=int(sys.argv[1]); fixture=sys.argv[2]
assert port in range(8089,8095)
base=f'http://127.0.0.1:{port}/'
cred=json.loads((root/f'.dev/{fixture}/test-credentials.json').read_text(encoding='utf-8-sig'))
client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
checks=[]; ids=[]
def request(path, data=None, authenticated=True):
    req=urllib.request.Request(base+path,data=urllib.parse.urlencode(data).encode() if data is not None else None)
    try:
        response=(client.open(req,timeout=45) if authenticated else urllib.request.urlopen(req,timeout=45))
    except urllib.error.HTTPError as error: response=error
    return response.status,response.read().decode('utf-8'),response.geturl()
def api(data):
    status,body,url=request('ws.php?format=json',data); result=json.loads(body);assert result['stat']=='ok',result;return result['result']
def check(ok, label):
    assert ok,label
    checks.append(label);print('PASS:',label,flush=True)
def page(path,data=None):
    if data is not None: data={'pwg_token':token,**data}
    return request(path,data)
api({'method':'pwg.session.login','username':cred['username'],'password':cred['password']})
token=api({'method':'pwg.session.getStatus'})['pwg_token']
manage='admin.php?page=plugin-gjallarblog-manage'
config='admin.php?page=plugin-gjallarblog-config'
def create(title,body):
    status,html,url=page(manage,{'submit_post':'1','post_header':title,'post_textarea':body})
    check(status==200 and 'edit=' in url,'Create redirects to the saved entry')
    ident=urllib.parse.parse_qs(urllib.parse.urlparse(url).query)['edit'][0];ids.append(ident);return ident,html,url
settings={'posts_per_page':'3','blog_name':'Journal 🌄 北','menu_label':'','blog_description':'é'*255,'date_format':'j F Y','show_date':'on'}
try:
    status,html,url=page(config,settings)
    check(status==200 and 'Settings saved.' in html,'Unicode settings and blank menu label save with feedback')
    check('Journal 🌄 北' in page('?/blog')[1],'Blank menu label falls back to Unicode journal title')
    quoted="Journal ' \" & <tag> \\ 🌄"
    status,html,url=page(config,{**settings,'blog_name':quoted})
    import html as html_lib
    check(status==200 and quoted in html_lib.unescape(html),'Quoted settings survive JSON and SQL round trip')
    page(config,settings)
    for field in ['blog_name','menu_label','blog_description','date_format','posts_per_page']:
        malformed={k:v for k,v in settings.items() if k!=field}
        malformed[field+'[]']='array'
        check(page(config,malformed)[0]==400,'Reject array-shaped '+field)
    check(page(config,{**settings,'show_number':'unexpected'})[0]==400,'Reject unknown checkbox value')
    for field in ['post_header','post_textarea']:
        malformed={'submit_post':'1','post_header':'Valid title','post_textarea':'Valid body'}
        malformed.pop(field);malformed[field+'[]']='array'
        check(page(manage,malformed)[0]==400,'Reject array-shaped '+field)
    for field in ['submit_post','preview']:
        check(page(manage,{field+'[]':'1','post_header':'Title','post_textarea':'Body'})[0]==400,'Reject array-shaped '+field)
    check(page(manage,{'submit_post':'1','post_header':'Invalid UTF8','post_textarea':b'\xff'})[0]==400,'Reject invalid UTF-8 body')
    check(page(manage,{'submit_post':'1','post_header':'Control','post_textarea':'bad\x00body'})[0]==400,'Reject control characters in body')
    bad={**settings,'blog_name':'é'*256,'blog_description':'Preserve this description'}
    status,html,url=page(config,bad)
    check(status==400 and 'Preserve this description' in html and 'aria-invalid="true"' in html,'Settings character limit returns field errors and retains other input')
    for key,value in [('blog_name',''),('date_format',''),('posts_per_page','0'),('posts_per_page','101'),('posts_per_page','1 OR 1=1'),('blog_name','Null\x00byte')]:
        check(page(config,{**settings,key:value})[0]==400,'Reject invalid setting '+key+' '+repr(value[:16]))
    status,html,url=page(config,{**settings,'show_date[]':'on'})
    check(status==400,'Reject array-shaped checkbox')
    for title in ['x'*256,'🌄'*256,'','  ','bad\x01title']:
        status,html,url=page(manage,{'submit_post':'1','post_header':title,'post_textarea':'<p>Keep my body</p>'})
        check(status==400 and 'Keep my body' in html,'Invalid title keeps body: '+repr(title[:10]))
    for title in ['x'*255,'🌄'*255,"Quotes ' \" <tag> & 北"]:
        ident,html,url=create(title,'<p>Boundary title body.</p>')
        check(title.replace('<','&lt;').replace('>','&gt;').replace('&lt;','&lt;')[:12] in html or '&#039;' in html,'Boundary title renders escaped')
    malicious='<p onclick="alert(1)">Safe <strong>bold</strong></p><script>alert(1)</script><img src="x" onerror="alert(1)" alt="test"><a href="javascript:alert(1)">bad link</a><iframe src="https://example.com"></iframe><svg onload="alert(1)"></svg>'
    ident,html,url=create('Sanitizer regression',malicious)
    public=page('?/blog/id/'+ident)[1]
    check('<strong>bold</strong>' in public and 'onclick=' not in public and 'onerror=' not in public and 'javascript:' not in public and '<iframe' not in public and '<svg' not in public,'Allow formatting; remove scripts, event attributes, unsafe URLs and active embeds')
    check('posted-monthly-list' not in public and 'created-monthly-list' not in public,'Journal does not inherit photo calendar actions')
    check('<h1>Sanitizer regression</h1>' in public and 'aria-current="location"' in public,'Entry has page heading and active journal navigation')
    check(public.count('Back to Journal')==2,'Entry has top and bottom return links')
    count_before=len(ids)
    status,html,url=page(manage,{'preview':'1','post_header':'Preview only','post_textarea':malicious})
    check(status==200 and 'sandbox=""' in html and 'Preview — not published' in html,'Preview is sandboxed and retains form data')
    check('Preview only' not in page('?/blog/feed')[1],'Preview does not publish')
    for size in [9998,9999,10000]:
        body='<p>'+('a'*(size-7))+'</p>'
        ident,html,url=create('Body length '+str(size),body)
        check('data-max="10000"' in html and 'gb-body-count-' in html,'Body limit '+str(size)+' accepted and counter exposed')
    status,html,url=page(manage,{'submit_post':'1','post_header':'Too long','post_textarea':'a'*10001})
    check(status==400 and '10000 characters' in html,'Reject body beyond 10,000 code points')
    status,html,url=page(manage,{'submit_post':'1','post_header':'Empty body','post_textarea':'<script>alert(1)</script>'})
    check(status==400,'Reject body that is empty after sanitation')
    first=ids[0]
    status,html,url=page(manage+'&npage=2',{'edit_post_id':first,'edit_post_header':'Edited oldest','edit_post_text':'<p>Changed.</p>'})
    check(status==200 and 'npage=3' in url and f'edit={first}' in url,'Editing returns to actual page and opens the edited entry')
    check('Entry updated.' in html,'Redirect displays one-time success message')
    refreshed=request(urllib.parse.urlsplit(url).path.lstrip('/')+'?'+urllib.parse.urlsplit(url).query)[1]
    check('Entry updated.' not in refreshed,'Refresh is a read and consumes the success notice once')
    before=page('?/blog/feed')[1]
    check(page(manage,{'del':first})[0]==400,'Deletion requires explicit confirmation')
    check(page('?/blog/feed')[1]==before,'Unconfirmed deletion changes no entries')
    feed=page('?/blog/feed')[1]
    check('javascript:' not in feed and 'onerror=' not in feed,'RSS uses sanitized bodies')
    home=page('?/blog/2')[1]
    check('aria-current="page"' in home and 'aria-label="Page 1"' in home,'Pagination exposes current page and useful names')
finally:
    for ident in ids: page(manage,{'del':ident,'confirm_delete':'1'})
    page(config,{'posts_per_page':'3','blog_name':'Field notes','menu_label':'Journal','blog_description':'Alpha compatibility','date_format':'j F Y','show_date':'on','show_on_main_page':'on'})
out=root/'docs/test-evidence'/f'{fixture}-usability.txt'
out.write_text('\n'.join('PASS: '+x for x in checks)+f'\nTotal: {len(checks)} checks\n',encoding='utf-8',newline='\n')
print(f'PASS: {len(checks)} usability/security checks on {fixture}')
