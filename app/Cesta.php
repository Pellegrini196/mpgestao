<?php
declare(strict_types=1);
namespace App;
final class Cesta {
    /** @var array<int,Produto> */
    private array $produtos = [];
    public function __construct(public readonly int $id, public readonly string $nome, public readonly Usuario $usuario) {}
    public function adicionar(Produto $produto): void { $this->produtos[$produto->id] = $produto; }
    public function produtos(): array { return array_values($this->produtos); }
    public function quantidade(): int { return count($this->produtos); }
    public function totalCentavos(): int { return array_sum(array_map(fn(Produto $p)=>$p->precoCentavos, $this->produtos)); }
}
