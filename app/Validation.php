<?php
declare(strict_types=1);
namespace App;
final class Validation {
    public static function text(array $data, string $key, int $max, bool $required=true): string {
        $v = $data[$key] ?? '';
        if (!is_string($v)) throw new \InvalidArgumentException('Campo inválido: '.$key);
        $v = trim($v);
        if (($required && $v==='') || strlen($v)>$max) throw new \InvalidArgumentException('Confira o campo '.$key.' (até '.$max.' caracteres).');
        return $v;
    }
    public static function id(mixed $value): int {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        if ($id === false) throw new \InvalidArgumentException('Identificador inválido.');
        return $id;
    }
    public static function email(array $data): string {
        $v = strtolower(self::text($data, 'email', 190));
        if (!filter_var($v, FILTER_VALIDATE_EMAIL)) throw new \InvalidArgumentException('Informe um e-mail válido.');
        return $v;
    }
    public static function cents(mixed $value): int {
        if (!is_string($value) || !preg_match('/^([0-9]{1,8})(?:[.,]([0-9]{1,2}))?$/', $value, $m)) throw new \InvalidArgumentException('Preço inválido. Use até duas casas decimais.');
        $c = (int)$m[1]*100 + (int)str_pad($m[2] ?? '', 2, '0');
        if ($c < 1) throw new \InvalidArgumentException('O preço deve ser maior que zero.');
        return $c;
    }
}
