<?php
require __DIR__.'/../app/bootstrap.php';
use App\{Auth,Database,Repository,Validation};
$page=is_string($_GET['page']??null)?$_GET['page']:'inicio';
$pages=['inicio','cadastros','atualizar','catalogo','cesta','login','registro'];
if (!in_array($page,$pages,true)) $page='inicio';
$error=null; $repo=null; $user=null; $lists=[];
try {
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        csrf(); $action=$_POST['acao']??'';
        if ($action==='logout') { $_SESSION=[]; session_destroy(); header('Location: ?page=login'); exit; }
        $db=Database::connect();
        if ($action==='registro') { (new Auth($db))->register($_POST); $_SESSION['flash']='Conta criada. Entre para continuar.'; header('Location: ?page=login'); exit; }
        elseif ($action==='login') { (new Auth($db))->login($_POST); header('Location: ?page=inicio'); exit; }
        else {
            $repo=new Repository($db,Auth::user());
            switch ($action) {
                case 'criar': $repo->save((string)($_POST['tipo']??''),$_POST); $_SESSION['flash']='Cadastro realizado.'; break;
                case 'adicionar': $repo->add(Validation::id($_POST['cesta_id']??null),$_POST['produtos']??[]); $_SESSION['flash']='Produtos incluídos. Cada produto aparece uma única vez.'; break;
                case 'remover': $repo->remove(Validation::id($_POST['cesta_id']??null),Validation::id($_POST['produto_id']??null)); $_SESSION['flash']='Produto removido.'; break;
                default: throw new InvalidArgumentException('Ação inválida.');
            }
            $target=$action==='criar'?'cadastros':'cesta';
            $suffix=isset($_POST['cesta_id'])?'&id='.Validation::id($_POST['cesta_id']):'';
            header('Location: ?page='.$target.$suffix); exit;
        }
    }
} catch (Throwable $e) {
    if ($e instanceof PDOException) { error_log((string)$e); $error=$e->getCode()==='23000'?'Este e-mail já está cadastrado ou há um relacionamento inválido.':'Falha na conexão ou operação do banco. Confira a instalação e configuração.'; }
    else $error=$e->getMessage();
}
if (!in_array($page,['login','registro'],true)) {
    if (empty($_SESSION['user'])) { header('Location: ?page=login'); exit; }
    try { $user=Auth::user(); $repo=new Repository(Database::connect(),$user); foreach (['fornecedores','produtos','cestas'] as $type) $lists[$type]=$repo->all($type); }
    catch (Throwable $e) { error_log((string)$e); $error='Banco indisponível. Execute php bin/install.php e confira config/local.php.'; $repo=null; }
}
function token(): void { echo '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function money(int $c): string { return 'R$ '.number_format($c/100,2,',','.'); }
function fields(string $type,array $row,array $suppliers): void { ?>
<label class="form-label">Nome<input class="form-control" name="nome" value="<?=e($row['nome']??'')?>" required maxlength="100"></label>
<?php if ($type==='fornecedores'): ?>
<label class="form-label">E-mail<input class="form-control" name="email" type="email" value="<?=e($row['email']??'')?>" required maxlength="190"></label>
<label class="form-label">Telefone <span class="muted">(opcional)</span><input class="form-control" name="telefone" value="<?=e($row['telefone']??'')?>" maxlength="30"></label>
<?php elseif ($type==='produtos'): ?>
<label class="form-label">Fornecedor<select class="form-select" name="fornecedor_id" required><option value="">Selecione</option><?php foreach ($suppliers as $s): ?><option value="<?=e($s['id'])?>" <?=($row['fornecedor_id']??'')==$s['id']?'selected':''?>><?=e($s['nome'])?></option><?php endforeach ?></select></label>
<label class="form-label">Preço (R$)<input class="form-control" name="preco" inputmode="decimal" placeholder="0,00" value="<?=e($row['preco']??'')?>" pattern="[0-9]{1,8}([.,][0-9]{1,2})?" required></label>
<label class="form-label">Descrição <span class="muted">(opcional)</span><textarea class="form-control" name="descricao" maxlength="500"><?=e($row['descricao']??'')?></textarea></label>
<?php endif; }
$names=['fornecedores'=>'Fornecedores','produtos'=>'Produtos','cestas'=>'Cestas'];
$titles=['inicio'=>'Tudo sob controle.','cadastros'=>'Organize sua base.','atualizar'=>'Atualize em tempo real.','catalogo'=>'Escolha seus produtos.','cesta'=>'Sua cesta, em detalhes.','login'=>'Bem-vindo de volta.','registro'=>'Comece por aqui.'];
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="<?=e($_SESSION['csrf'])?>"><title><?=e($titles[$page])?> | mpGESTÃO</title><link rel="stylesheet" href="assets/bootstrap.min.css"><link rel="stylesheet" href="assets/app.css"><script src="assets/app.js" defer></script></head><body>
<header><div class="container topbar"><a class="brand" href="?page=inicio"><span class="brand-icon">&gt;mp_</span> GESTÃO<span class="brand-sub">GESTÃO DE PRODUTOS</span></a><?php if ($user): ?><div class="account"><span><?=e($user->nome)?></span><form method="post"><?php token();?><input type="hidden" name="acao" value="logout"><button class="btn btn-outline-light btn-sm">Sair</button></form></div><?php endif ?></div></header>
<?php if ($user): ?><nav class="container navigation" aria-label="Menu principal"><?php foreach (['inicio'=>'Visão geral','cadastros'=>'Cadastros','atualizar'=>'Atualizar · AJAX','catalogo'=>'Catálogo','cesta'=>'Minhas cestas'] as $url=>$label): ?><a class="<?=$page===$url?'active':''?>" href="?page=<?=$url?>"><?=e($label)?></a><?php endforeach ?></nav><?php endif ?>
<main class="container"><div class="page-title"><p class="eyebrow">MP SOFTWARE / GESTÃO DE PRODUTOS</p><h1><?=e($titles[$page])?></h1><p class="muted">Fornecedores, produtos e cestas conectados em um só lugar.</p></div>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?=e($error)?></div><?php endif ?>
<?php if (isset($_SESSION['flash'])): ?><div class="alert alert-success" role="status"><?=e($_SESSION['flash'])?></div><?php unset($_SESSION['flash']); endif ?>
<?php if ($page==='login'||$page==='registro'): ?>
<section class="auth card"><div class="card-body"><h2><?=$page==='login'?'Acesse sua conta':'Crie sua conta'?></h2><form method="post"><?php token(); ?><input type="hidden" name="acao" value="<?=$page?>">
<?php if ($page==='registro'): ?><label class="form-label">Nome<input class="form-control" name="nome" required maxlength="100" autocomplete="name"></label><?php endif ?>
<label class="form-label">E-mail<input class="form-control" name="email" type="email" required maxlength="190" autocomplete="username"></label>
<label class="form-label">Senha<input class="form-control" name="senha" type="password" required <?=$page==='registro'?'minlength="8"':''?> maxlength="128" autocomplete="<?=$page==='registro'?'new-password':'current-password'?>"></label>
<?php if ($page==='registro'): ?><small>Use de 8 a 128 caracteres.</small><label class="form-label">Confirme a senha<input class="form-control" name="confirmacao" type="password" required autocomplete="new-password"></label><?php endif ?>
<button class="btn btn-primary w-100"><?=$page==='login'?'Entrar':'Criar conta'?></button></form><p class="mt-3 mb-0"><?=$page==='login'?'Ainda não tem conta?':'Já tem conta?'?> <a href="?page=<?=$page==='login'?'registro':'login'?>"><?=$page==='login'?'Cadastre-se':'Entrar'?></a></p></div></section>
<?php elseif ($repo): ?>
<?php if ($page==='inicio'): ?>
<div class="row g-4"><?php foreach ($names as $type=>$label): ?><div class="col-md-4"><article class="card metric"><p><?=e($label)?></p><strong><?=count($lists[$type])?></strong><span class="muted">cadastrados por você</span></article></div><?php endforeach ?></div>
<section class="welcome"><div><p class="eyebrow">DA BASE À CESTA</p><h2>Uma seleção bem organizada<br>começa com bons cadastros.</h2><p>Cadastre um fornecedor, vincule seus produtos e monte sua primeira cesta.</p><a class="btn btn-primary" href="?page=cadastros">Começar cadastro →</a></div><div class="steps"><p><b>01</b> Cadastre fornecedores</p><p><b>02</b> Adicione produtos e crie uma cesta</p><p><b>03</b> Selecione e confira o total</p></div></section>
<?php elseif ($page==='cadastros'): ?>
<p class="muted">Comece pelo fornecedor. Depois cadastre os produtos e dê um nome à sua cesta.</p><div class="row g-4"><?php foreach ($names as $type=>$label): ?><div class="col-lg-4"><section class="card h-100"><div class="card-body"><h2><?=e($label)?></h2><form method="post"><?php token(); ?><input type="hidden" name="acao" value="criar"><input type="hidden" name="tipo" value="<?=$type?>"><?php fields($type,[],$lists['fornecedores']); ?><?php if ($type==='produtos'&&!$lists['fornecedores']): ?><p class="muted">Cadastre um fornecedor primeiro.</p><?php endif ?><button class="btn btn-primary" <?=$type==='produtos'&&!$lists['fornecedores']?'disabled':''?>>Cadastrar</button></form></div></section></div><?php endforeach ?></div>
<?php elseif ($page==='atualizar'): ?>
<p class="muted">Edite os campos e salve. A atualização usa AJAX, sem recarregar esta página.</p>
<?php foreach ($names as $type=>$label): ?><section class="edit-section"><h2><?=e($label)?> <span class="badge text-bg-light"><?=count($lists[$type])?></span></h2><?php if (!$lists[$type]): ?><p class="empty">Nenhum registro. <a href="?page=cadastros">Cadastre o primeiro.</a></p><?php endif ?><div class="row g-3"><?php foreach ($lists[$type] as $row): ?><div class="col-md-6 col-xl-4"><form class="card card-body ajax-edit"><input type="hidden" name="tipo" value="<?=$type?>"><input type="hidden" name="id" value="<?=e($row['id'])?>"><?php fields($type,$row,$lists['fornecedores']); ?><button class="btn btn-outline-primary">Salvar alterações</button><p class="form-status mb-0 mt-2" role="status" aria-live="polite"></p></form></div><?php endforeach ?></div></section><?php endforeach ?>
<?php elseif ($page==='catalogo'): ?>
<?php if (!$lists['produtos']||!$lists['cestas']): ?><div class="empty"><h2>Vamos preparar sua seleção?</h2><p>Você precisa de produtos e de pelo menos uma cesta cadastrada.</p><a class="btn btn-primary" href="?page=cadastros">Ir para cadastros</a></div><?php else: ?>
<form method="post" id="catalog-form"><?php token(); ?><input type="hidden" name="acao" value="adicionar"><div class="selection-bar"><label>Cesta de destino<select class="form-select" name="cesta_id" required><?php foreach ($lists['cestas'] as $b): ?><option value="<?=e($b['id'])?>"><?=e($b['nome'])?></option><?php endforeach ?></select></label><p id="selected-count" role="status">0 produtos selecionados</p><button class="btn btn-primary" id="add-button" disabled>Incluir na cesta →</button></div><div class="row g-4"><?php foreach ($lists['produtos'] as $p): $supplier=$repo->get('fornecedores',(int)$p['fornecedor_id']); ?><div class="col-md-6 col-lg-4"><label class="card product-card"><div class="product-top"><span class="product-symbol">P</span><input class="form-check-input product-check" type="checkbox" name="produtos[]" value="<?=e($p['id'])?>" aria-label="Selecionar <?=e($p['nome'])?>"></div><div class="card-body"><small class="muted"><?=e($supplier['nome'])?></small><h2><?=e($p['nome'])?></h2><p><?=e($p['descricao'])?></p><strong class="price"><?=money(Validation::cents($p['preco']))?></strong><small class="muted d-block">Uma unidade por cesta</small></div></label></div><?php endforeach ?></div></form><?php endif ?>
<?php elseif ($page==='cesta'): ?>
<?php if (!$lists['cestas']): ?><div class="empty"><h2>Sua primeira cesta começa aqui.</h2><a class="btn btn-primary" href="?page=cadastros">Criar cesta</a></div><?php else: ?>
<form method="get" class="basket-picker"><input type="hidden" name="page" value="cesta"><label>Cesta<select class="form-select" name="id"><?php foreach ($lists['cestas'] as $b): ?><option value="<?=e($b['id'])?>" <?=($_GET['id']??$lists['cestas'][0]['id'])==$b['id']?'selected':''?>><?=e($b['nome'])?></option><?php endforeach ?></select></label><button class="btn btn-outline-primary">Visualizar</button></form>
<?php try { $basket=$repo->basket(Validation::id($_GET['id']??$lists['cestas'][0]['id'])); ?>
<div class="row g-4"><div class="col-lg-8"><section class="card card-body"><h2><?=e($basket->nome)?></h2><?php if (!$basket->quantidade()): ?><p class="empty">Cesta vazia. <a href="?page=catalogo">Escolha produtos no catálogo.</a></p><?php else: ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Produto / fornecedor</th><th>Valor</th><th>Ação</th></tr></thead><tbody><?php foreach ($basket->produtos() as $p): ?><tr><td><strong><?=e($p->nome)?></strong><small class="d-block muted"><?=e($p->fornecedor->nome)?></small></td><td class="text-nowrap"><?=money($p->precoCentavos)?></td><td><form method="post"><?php token(); ?><input type="hidden" name="acao" value="remover"><input type="hidden" name="cesta_id" value="<?=$basket->id?>"><input type="hidden" name="produto_id" value="<?=$p->id?>"><button class="btn btn-sm btn-outline-danger" aria-label="Remover <?=e($p->nome)?>">Remover</button></form></td></tr><?php endforeach ?></tbody></table></div><?php endif ?></section></div><div class="col-lg-4"><aside class="summary"><p class="eyebrow">RESUMO DA CESTA</p><p>Produtos selecionados <strong><?=$basket->quantidade()?></strong></p><hr><span>Total</span><strong class="total"><?=money($basket->totalCentavos())?></strong><p>Uma unidade de cada produto.<br>Valores atualizados conforme o cadastro.</p><a class="btn btn-primary w-100" href="?page=catalogo">Adicionar produtos</a></aside></div></div>
<?php } catch (Throwable $e) { ?><div class="alert alert-danger">Cesta não encontrada ou indisponível.</div><?php } endif ?>
<?php endif; endif ?>
</main><footer class="container">mpGESTÃO · Mini Sistema de Gestão de Produtos <span>Projeto acadêmico • PHP + MySQL</span></footer></body></html>
