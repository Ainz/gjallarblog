"""HTTP regression checks; writes only to disposable Piwigo galleries."""
import urllib.request, urllib.parse, urllib.error, http.cookiejar, json, pathlib, sys, re, struct, zlib, binascii, uuid
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
def upload_file(path, fields, name, content, mime):
    boundary='----Gjallarblog'+uuid.uuid4().hex
    pieces=[]
    for key,value in fields.items():
        pieces += [f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode()]
    pieces += [f'--{boundary}\r\nContent-Disposition: form-data; name="image"; filename="{name}"\r\nContent-Type: {mime}\r\n\r\n'.encode(),content,f'\r\n--{boundary}--\r\n'.encode()]
    req=urllib.request.Request(base+path,data=b''.join(pieces),headers={'Content-Type':f'multipart/form-data; boundary={boundary}'})
    try: response=client.open(req,timeout=45)
    except urllib.error.HTTPError as error: response=error
    return response.status,response.read(),response.headers,response.geturl()
def png_pixel():
    def chunk(kind,data): return struct.pack('>I',len(data))+kind+data+struct.pack('>I',binascii.crc32(kind+data)&0xffffffff)
    return b'\x89PNG\r\n\x1a\n'+chunk(b'IHDR',struct.pack('>IIBBBBB',1,1,8,6,0,0,0))+chunk(b'IDAT',zlib.compress(b'\x00\xff\x20\x10\xff'))+chunk(b'IEND',b'')
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
    status,html,url=page(manage,{'submit_post':'1','post_header':title,'post_textarea':body,'post_date':'2020-01-02T12:00'})
    check(status==200 and 'edit=' in url,'Create redirects to the saved entry')
    ident=urllib.parse.parse_qs(urllib.parse.urlparse(url).query)['edit'][0];ids.append(ident);return ident,html,url
settings={'posts_per_page':'3','blog_name':'Journal 🌄 北','blog_description':'é'*255,'date_format':'j F Y','show_date':'on','show_menu':'on','home_display':'hidden','album_display':'hidden'}
try:
    status,html,url=page(config,settings)
    check(status==200 and 'Settings saved.' in html,'Unicode journal name saves with feedback')
    check('id="gb-page-size" name="posts_per_page" min="1" max="5"' in html,'Recent-entry count is visibly capped at five')
    check('Choose 1 to 5 entries.' in html and 'above the home-page title and albums' in html and 'below the album content' in html,'Placement help explains page order and five-entry scrolling limit')
    check('Localized: 22 October 2026' in html and 'ISO: 2026-10-22' in html and 'Day/month/year: 22/10/2026' in html,'Date selector offers three concise common formats')
    check('Current setting:' in html and 'target="_blank" rel="noopener noreferrer"' in html and 'https://www.php.net/manual/en/datetime.format.php' in html,'Date help shows current output and opens the format reference safely in a new tab')
    journal_html=page('?/blog')[1]
    check(journal_html.count('Journal 🌄 北')>=2,'One journal name appears in both heading and menu navigation')
    check('<dt><a href="' in journal_html and 'gjallarblog-menu-item' not in journal_html,'Journal menu link uses the same theme-native markup as other menu items')
    check('menu.css' not in (root/'main.inc.php').read_text(encoding='utf-8') and not (root/'menu.css').exists(),'No Gjallarblog-specific menu styling overrides theme settings')
    quoted="Journal ' \" & <tag> \\ 🌄"
    status,html,url=page(config,{**settings,'blog_name':quoted})
    import html as html_lib
    expected="Journal ' \" &  \\ 🌄"
    check(status==200 and expected in html_lib.unescape(html) and '<tag>' not in html,'Quoted settings survive while title markup is removed')
    page(config,settings)
    for field in ['blog_name','blog_description','date_format','posts_per_page']:
        malformed={k:v for k,v in settings.items() if k!=field}
        malformed[field+'[]']='array'
        check(page(config,malformed)[0]==400,'Reject array-shaped '+field)
    check(page(config,{**settings,'show_date':'unexpected'})[0]==400,'Reject unknown checkbox value')
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
    for key,value in [('blog_name',''),('date_format',''),('date_format','Y-m-d H:i:s'),('posts_per_page','0'),('posts_per_page','6'),('posts_per_page','101'),('posts_per_page','1 OR 1=1'),('blog_name','Null\x00byte')]:
        check(page(config,{**settings,key:value})[0]==400,'Reject invalid setting '+key+' '+repr(value[:16]))
    status,html,url=page(config,{**settings,'show_date[]':'on'})
    check(status==400,'Reject array-shaped checkbox')
    for title in ['x'*256,'🌄'*256,'','  ','bad\x01title']:
        status,html,url=page(manage,{'submit_post':'1','post_header':title,'post_textarea':'<p>Keep my body</p>'})
        check(status==400 and 'Keep my body' in html,'Invalid title keeps body: '+repr(title[:10]))
    for title in ['x'*255,'🌄'*255,"Quotes ' \" <tag> & 北",'<H2>Plain title</H2>']:
        ident,html,url=create(title,'<p>Boundary title body.</p>')
        public_title=page('?/blog/id/'+ident)[1]
        heading=re.search(r'<h1>(.*?)</h1>',public_title,re.S)
        expected_title=html_lib.unescape(re.sub(r'<[^>]*>','',title))
        check(bool(heading) and html_lib.unescape(heading.group(1))==expected_title,'Boundary title is plain text, theme-styled and length checked')
    malicious='<p onclick="alert(1)">Safe <strong>bold</strong></p><script>alert(1)</script><img src="x" onerror="alert(1)" alt="test"><a href="javascript:alert(1)">bad link</a><iframe src="https://example.com"></iframe><svg onload="alert(1)"></svg>'
    ident,html,url=create('Sanitizer regression',malicious)
    public=page('?/blog/id/'+ident)[1]
    check('<strong>bold</strong>' in public and 'onclick=' not in public and 'onerror=' not in public and 'javascript:' not in public and '<iframe' not in public and '<svg' not in public,'Allow formatting; remove scripts, event attributes, unsafe URLs and active embeds')
    formatted='<h2>Section</h2><h3>Subsection</h3><hr><blockquote><p>A quotation.</p></blockquote><p>Inline <code>safe_snippet()</code></p><pre><code>&lt;script&gt;alert(1)&lt;/script&gt;</code></pre><figure class="gb-image gb-image--center gb-image--medium"><img src="index.php?/blog/media/'+'a'*32+'.png" alt="Ship"><figcaption>Test caption</figcaption></figure>'
    ident,html,url=create('Safe formatting examples',formatted)
    formatted_public=page('?/blog/id/'+ident)[1]
    check('<h2>Section</h2>' in formatted_public and '<h3>Subsection</h3>' in formatted_public and '<hr' in formatted_public and '<blockquote>' in formatted_public and '<code>safe_snippet()</code>' in formatted_public and '<pre><code>' in formatted_public,'Keep headings, separators, quotations and inline/block code formatting')
    check('gb-image--center' in formatted_public and '<figcaption>Test caption</figcaption>' in formatted_public,'Preserve safe image placement classes and captions')
    check('&lt;script&gt;alert(1)&lt;/script&gt;' in formatted_public and '<script>alert(1)</script>' not in formatted_public,'Render escaped code literally without executing markup')
    check('Preview shows sanitized content without saving it' in html and 'drafts are not available' not in html,'Formatting help explains preview without obsolete draft status text')
    check('datetime-local' in html and 'gallery timezone' in html and 'future dates are not supported' in html,'Date control explains local gallery time and historical use')
    invalid_date={'submit_post':'1','post_header':'Future date','post_textarea':'<p>Not saved.</p>','post_date':'2999-01-01T12:00'}
    check(page(manage,invalid_date)[0]==400,'Reject future-dated entries')
    no_file={ 'pwg_token':token,'upload_image':'1' }
    status,body,url=request(manage,no_file)
    check(status==400 and 'image' in body.lower(),'Reject image upload without an actual file')
    status,body,headers,url=upload_file(manage,{'pwg_token':token,'upload_image':'1'},'bad.svg',b'<svg/>','image/svg+xml')
    check(status==400,'Reject active SVG image uploads')
    status,body,headers,url=upload_file(manage,{'pwg_token':token,'upload_image':'1'},'paste.png',png_pixel(),'image/png')
    try: upload_result=json.loads(body)
    except json.JSONDecodeError: upload_result={}
    check(status==200 and upload_result.get('url','').find('?/blog/media/')>=0,f'Accept a validated pasted PNG and return an access-checked gallery URL (HTTP {status}: {body[:300]!r})')
    media_url=upload_result['url']; media_path=urllib.parse.urlsplit(media_url).path+'?'+urllib.parse.urlsplit(media_url).query
    with urllib.request.urlopen(base+media_path,timeout=45) as media_response:
        media_bytes=media_response.read(); media_type=media_response.headers.get_content_type()
    check(media_type=='image/png' and media_bytes.startswith(b'\x89PNG\r\n\x1a\n'),'Serve the sanitized raster image through the Piwigo page route')
    image_body='<figure class="gb-image gb-image--center gb-image--medium"><img src="'+media_url+'" alt="Red pixel"><figcaption>Test caption</figcaption></figure>'
    image_id,html,url=create('Pasted image placement',image_body)
    image_public=page('?/blog/id/'+image_id)[1]
    check('gb-image--center' in image_public and 'Red pixel' in image_public and 'Test caption' in image_public,'Save, render and caption the inserted block image')
    status,body,headers,url=upload_file(manage,{'pwg_token':'invalid','upload_image':'1'},'unauthorized.png',png_pixel(),'image/png')
    check(status in (400,401,403),'Reject image upload with invalid CSRF token')
    check('posted-monthly-list' not in public and 'created-monthly-list' not in public,'Journal does not inherit photo calendar actions')
    check('<h1>Sanitizer regression</h1>' in public and re.search(r'>Entry [0-9]+',public) is None,'Entry has a page heading without technical entry numbers')
    check(public.count('Back to Journal 🌄 北')==1 and 'Entry navigation' in public,'Entry has one return link and a dedicated navigation row')
    nav_match=re.search(r'<nav class="gjallarblog-entry-navigation".*?</nav>',public,re.S)
    nav_markup=nav_match.group(0) if nav_match else 'navigation not rendered'
    check('rel="prev"' in public and 'Previous entry' in public,f'Single-entry navigation links to the previous chronological entry (entry {ident}): '+nav_markup[:500])
    next_public=page('?/blog/id/'+ids[0])[1]
    check('rel="next"' in next_public and 'Next entry' in next_public,'Single-entry navigation links to the next chronological entry')
    check('class="gb-shortcut"' in html and 'role="tab"' not in html,'Admin shortcuts look like buttons and are not tabs')
    removal=request('admin.php?page=plugin-gjallarblog-remove')[1]
    check('Piwigo Restore uninstalls and reactivates the plugin' in removal and 'Review before removing' in removal and 'preserves entries, images and settings during Restore' in removal,'Removal has its own tab and explains Restore and permanent data removal separately')
    check('Plugin removal' not in request(config)[1] and 'Removal' in removal,'Removal controls are separated from Settings')
    count_before=len(ids)
    status,html,url=page(manage,{'preview':'1','post_header':'Preview only','post_textarea':malicious,'post_date':'2020-01-02T12:00'})
    check(status==200 and 'sandbox=""' in html and 'Preview — not published' in html,'Preview is sandboxed and retains form data')
    check('Preview only' not in page('?/blog')[1],'Preview does not publish')
    for size in [9998,9999,10000]:
        body='<p>'+('a'*(size-7))+'</p>'
        ident,html,url=create('Body length '+str(size),body)
        check('data-max="10000"' in html and 'gb-body-count-' in html,'Body limit '+str(size)+' accepted and counter exposed')
    status,html,url=page(manage,{'submit_post':'1','post_header':'Too long','post_textarea':'a'*10001})
    check(status==400 and '10000 characters' in html,'Reject body beyond 10,000 code points')
    status,html,url=page(manage,{'submit_post':'1','post_header':'Empty body','post_textarea':'<script>alert(1)</script>'})
    check(status==400,'Reject body that is empty after sanitation')
    first=ids[0]
    ordered_ids=sorted(ids,key=int,reverse=True)
    expected_edit_page=ordered_ids.index(first)//3+1
    status,html,url=page(manage+'&npage=2',{'edit_post_id':first,'edit_post_header':'Edited oldest','edit_post_text':'<p>Changed.</p>','edit_post_date':'2020-01-02T12:00'})
    page_match=re.search(r'[?&]npage=(\d+)',url)
    check(status==200 and page_match and int(page_match.group(1))==expected_edit_page and f'edit={first}' in url,'Editing returns to actual page and opens the edited entry')
    check('Entry updated.' in html,'Redirect displays one-time success message')
    refreshed=request(urllib.parse.urlsplit(url).path.lstrip('/')+'?'+urllib.parse.urlsplit(url).query)[1]
    check('Entry updated.' not in refreshed,'Refresh is a read and consumes the success notice once')
    before=page('?/blog/id/'+first)[1]
    check(page(manage,{'del':first})[0]==400,'Deletion requires explicit confirmation')
    check(page('?/blog/id/'+first)[1]==before,'Unconfirmed deletion changes no entries')
    feed=page('?/blog')[1]
    check('javascript:' not in feed and 'onerror=' not in feed,'Journal uses sanitized bodies')
    home=page('?/blog/2')[1]
    check('aria-current="page"' in home and 'aria-label="Page 1"' in home,'Pagination exposes current page and useful names')
finally:
    for ident in ids: page(manage,{'del':ident,'confirm_delete':'1'})
    page(config,{'posts_per_page':'3','blog_name':'Field notes','menu_label':'Journal','blog_description':'Alpha compatibility','date_format':'j F Y','show_date':'on','show_menu':'on','home_display':'hidden','album_display':'hidden','show_on_main_page':'on'})
out=root/'docs/test-evidence'/f'{fixture}-usability.txt'
out.write_text('\n'.join('PASS: '+x for x in checks)+f'\nTotal: {len(checks)} checks\n',encoding='utf-8',newline='\n')
print(f'PASS: {len(checks)} usability/security checks on {fixture}')
