<?php
declare(strict_types=1);
namespace App;
use PDO;
final class Auth {
    public function __construct(private PDO $db) {}
    public function register(array $data): void {
        $name = Validation::text($data, 'nome', 100);
        $email = Validation::email($data);
        $password = $data['senha'] ?? '';
        if (!is_string($password) || strlen($password)<8 || strlen($password)>128) throw new \InvalidArgumentException('A senha deve ter entre 8 e 128 caracteres.');
        if ($password !== ($data['confirmacao'] ?? '')) throw new \InvalidArgumentException('As senhas não coincidem.');
        $salt = bin2hex(random_bytes(16));
        $q = $this->db->prepare('INSERT INTO usuarios(nome,email,senha_hash,senha_salt) VALUES (?,?,?,?)');
        $q->execute([$name, $email, hash('sha256', $salt.$password), $salt]);
    }
    public function login(array $data): void {
        $email = Validation::email($data);
        $q = $this->db->prepare('SELECT * FROM usuarios WHERE email=?'); $q->execute([$email]); $u=$q->fetch();
        $password = $data['senha'] ?? '';
        if (!is_string($password) || !$u || !hash_equals($u['senha_hash'], hash('sha256', $u['senha_salt'].$password))) throw new \InvalidArgumentException('E-mail ou senha incorretos.');
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>(int)$u['id'], 'nome'=>$u['nome'], 'email'=>$u['email']];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    public static function user(): Usuario {
        $u = $_SESSION['user'] ?? null;
        if (!$u) throw new \RuntimeException('Faça login para continuar.', 401);
        return new Usuario($u['id'], $u['nome'], $u['email']);
    }
}
