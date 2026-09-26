<?php
require __DIR__.'/../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $user=App\Auth::user();
    if ($_SERVER['REQUEST_METHOD']!=='POST') throw new RuntimeException('Método não permitido.',405);
    csrf();
    $data=json_decode(file_get_contents('php://input'),true,512,JSON_THROW_ON_ERROR);
    if (!is_array($data)) throw new InvalidArgumentException('Dados inválidos.');
    $repo=new App\Repository(App\Database::connect(),$user);
    $id=$repo->save((string)($data['tipo']??''),$data,App\Validation::id($data['id']??null));
    echo json_encode(['ok'=>true,'message'=>'Alterações salvas sem recarregar a página.','id'=>$id],JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    $status=$e instanceof PDOException ? 500 : (in_array($e->getCode(),[401,403,404,405],true)?$e->getCode():422);
    http_response_code($status);
    if ($e instanceof PDOException) error_log((string)$e);
    echo json_encode(['ok'=>false,'message'=>$e instanceof PDOException?'Não foi possível salvar. Verifique os dados e tente novamente.':$e->getMessage()],JSON_UNESCAPED_UNICODE);
}
