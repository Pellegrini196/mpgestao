<?php
declare(strict_types=1);
namespace App;
final class Usuario {
    public function __construct(public readonly int $id, public readonly string $nome, public readonly string $email) {}
}
