<?php
require __DIR__.'/../app/bootstrap.php';
use App\{Usuario,Fornecedor,Produto,Cesta,Validation};
function check(bool $ok,string $label): void { if (!$ok) throw new RuntimeException($label); echo "OK: $label\n"; }
check(Validation::cents('10,25')===1025,'moeda com vírgula');
check(Validation::cents('0.01')===1,'centavo exato');
foreach (['0','-1','1.234','1e3',[],null] as $bad) { try { Validation::cents($bad); throw new RuntimeException('Preço inválido aceito'); } catch (InvalidArgumentException $e) {} }
check(true,'preços inválidos rejeitados');
$u=new Usuario(1,'Teste','teste@example.test'); $f=new Fornecedor(1,'Fornecedor');
$b=new Cesta(1,'Cesta',$u); $p=new Produto(1,'P1',1025,$f);
$b->adicionar($p); $b->adicionar($p); $b->adicionar(new Produto(2,'P2',1,$f));
check($b->quantidade()===2,'uma unidade por produto');
check($b->totalCentavos()===1026,'total sem erro de ponto flutuante');
check($b->produtos()[0]->fornecedor===$f,'relacionamento entre objetos');
check(e('<script>')==='&lt;script&gt;','escape de HTML');
