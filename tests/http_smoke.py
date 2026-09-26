"""Smoke HTTP: execute com servidor e banco de testes ativos, python tests/http_smoke.py URL."""
import sys, urllib.request,urllib.parse,http.cookiejar,json,re,uuid
base=sys.argv[1] if len(sys.argv)>1 else 'http://127.0.0.1:8000'
jar=http.cookiejar.CookieJar(); client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
def call(path='',data=None,headers=None):
 req=urllib.request.Request(base+'/'+path,data=data,headers=headers or {})
 try:
  with client.open(req) as r:return r.status,r.read().decode()
 except urllib.error.HTTPError as e:return e.code,e.read().decode()
def csrf():
 _,h=call('?page=login');return re.search(r'name="csrf-token" content="([^"]+)',h).group(1)
def post(page,data):return call('?page='+page,urllib.parse.urlencode(data,doseq=True).encode())
token=csrf();email=uuid.uuid4().hex+'@example.test'
status,h=post('registro',dict(csrf=token,acao='registro',nome='HTTP Test',email=email,senha='Teste-123456',confirmacao='Teste-123456'))
assert 'Conta criada' in h, re.findall(r'<div class="alert.*?</div>',h)
status,h=post('login',dict(csrf=token,acao='login',email=email,senha='Teste-123456'))
assert 'Tudo sob controle' in h
token=re.search(r'name="csrf-token" content="([^"]+)',h).group(1)
for tipo,data in [('fornecedores',dict(nome='Fornecedor HTTP',email='http@example.test')),('cestas',dict(nome='Cesta HTTP'))]:
 _,h=post('cadastros',dict(csrf=token,acao='criar',tipo=tipo,**data));assert 'Cadastro realizado' in h
_,h=call('?page=atualizar')
fid=re.search(r'name="tipo" value="fornecedores"><input type="hidden" name="id" value="(\d+)',h).group(1)
bid=re.search(r'name="tipo" value="cestas"><input type="hidden" name="id" value="(\d+)',h).group(1)
_,h=post('cadastros',dict(csrf=token,acao='criar',tipo='produtos',nome='Produto HTTP',preco='12.34',fornecedor_id=fid));assert 'Cadastro realizado' in h
_,h=call('?page=catalogo');pid=re.search(r'name="produtos\[\]" value="(\d+)',h).group(1)
_,h=post('catalogo',dict(csrf=token,acao='adicionar',cesta_id=bid,**{'produtos[]':[pid,pid]}));assert 'R$ 12,34' in h
headers={'Content-Type':'application/json','X-CSRF-Token':token}
for tipo,record,data in [('fornecedores',fid,dict(nome='Fornecedor alterado',email='http@example.test')),('produtos',pid,dict(nome='Produto alterado',preco='20.10',fornecedor_id=fid)),('cestas',bid,dict(nome='Cesta alterada'))]:
 status,result=call('api.php',json.dumps(dict(tipo=tipo,id=record,**data)).encode(),headers);assert status==200 and json.loads(result)['ok']
_,h=call('?page=cesta&id='+bid);assert 'R$ 20,10' in h and 'Cesta alterada' in h and 'Fornecedor alterado' in h
status,_=call('api.php',b'{}',{'Content-Type':'application/json'});assert status==403
post('login',dict(csrf=token,acao='logout'))
status,_=call('api.php',b'{}',headers);assert status==401
print('OK: cadastro, login, cadastros, seleção, total, edição AJAX dos 3 elementos, CSRF e logout via HTTP')
