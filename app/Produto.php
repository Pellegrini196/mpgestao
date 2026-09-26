<?php
declare(strict_types=1);
namespace App;
final class Produto {
    public function __construct(public readonly int $id, public readonly string $nome, public readonly int $precoCentavos, public readonly Fornecedor $fornecedor) {}
}
