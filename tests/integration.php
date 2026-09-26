<?php
require __DIR__.'/../app/bootstrap.php';
use App\{Database,Auth,Repository,Usuario};
$c=require __DIR__.'/../config/database.php';
if (!str_ends_with($c['name'],'_test')) { fwrite(STDERR,"Use um banco exclusivo com sufixo _test.\n"); exit(1); }
function check(bool $ok,string $label): void { if (!$ok) throw new RuntimeException($label); echo "OK: $label\n"; }
function rejected(callable $f,string $label): void { try {$f();} catch (Throwable $e) {echo "OK: $label\n"; return;} throw new RuntimeException($label); }
Database::install(); Database::install(); $db=Database::connect(); $ids=[];
try {
 $auth=new Auth($db); $unique=bin2hex(random_bytes(6));
 foreach (['A','B'] as $name) {
  $email=strtolower($name).$unique.'@example.test';
  $data=['nome'=>$name,'email'=>$email,'senha'=>'Teste-123456','confirmacao'=>'Teste-123456'];
  $auth->register($data); $ids[]=(int)$db->lastInsertId();
  rejected(fn()=>$auth->register($data),'e-mail duplicado rejeitado');
 }
 $q=$db->prepare('SELECT * FROM usuarios WHERE id=?');$q->execute([$ids[0]]);$row=$q->fetch();
 check($row['senha_hash']===hash('sha256',$row['senha_salt'].'Teste-123456'),'hash SHA-256 persistido com salt');
 $a=new Repository($db,new Usuario($ids[0],'A','a@example.test')); $b=new Repository($db,new Usuario($ids[1],'B','b@example.test'));
 $f=$a->save('fornecedores',['nome'=>'Fornecedor','email'=>'f@example.test']);
 $p=$a->save('produtos',['nome'=>'Produto','preco'=>'12,34','fornecedor_id'=>$f]);
 $basket=$a->save('cestas',['nome'=>'Cesta']);
 rejected(fn()=>$a->add($basket,[]),'seleção vazia rejeitada');
 $a->add($basket,[$p,$p]);$a->add($basket,[$p]);
 check($a->basket($basket)->quantidade()===1,'duplicidade impedida no banco');
 check($a->basket($basket)->totalCentavos()===1234,'total persistido');
 $a->save('produtos',['nome'=>'Produto editado','preco'=>'20.10','fornecedor_id'=>$f],$p);
 check($a->basket($basket)->totalCentavos()===2010,'resumo acompanha atualização de preço');
 rejected(fn()=>$b->get('cestas',$basket),'cesta alheia bloqueada');
 rejected(fn()=>$b->save('produtos',['nome'=>'Intruso','preco'=>'1','fornecedor_id'=>$f]),'fornecedor alheio bloqueado');
 rejected(fn()=>$b->save('cestas',['nome'=>'Intruso'],$basket),'edição alheia bloqueada');
 $other=$b->save('cestas',['nome'=>'Outra']);
 rejected(fn()=>$b->add($other,[$p]),'produto alheio bloqueado');
 $a->remove($basket,$p); check($a->basket($basket)->quantidade()===0,'remoção persistida');
 echo "Integração concluída.\n";
} finally {
 foreach ($ids as $id) {
  foreach (['cesta_produtos','cestas','produtos','fornecedores'] as $table) $db->prepare("DELETE FROM $table WHERE usuario_id=?")->execute([$id]);
  $db->prepare('DELETE FROM usuarios WHERE id=?')->execute([$id]);
 }
}
