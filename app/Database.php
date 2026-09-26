<?php
declare(strict_types=1);
namespace App;
use PDO;
final class Database {
    public static function connect(bool $withDatabase = true): PDO {
        $c = require __DIR__.'/../config/database.php';
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $c['name'])) throw new \RuntimeException('Nome do banco inválido.');
        $dsn = "mysql:host={$c['host']};port={$c['port']};charset=utf8mb4";
        if ($withDatabase) $dsn .= ";dbname={$c['name']}";
        return new PDO($dsn, $c['user'], $c['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
    }
    public static function install(): void {
        $c = require __DIR__.'/../config/database.php';
        $pdo = self::connect(false);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$c['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$c['name']}`");
        foreach (explode(';', file_get_contents(__DIR__.'/../database/schema.sql')) as $sql) {
            if (trim($sql) !== '') $pdo->exec($sql);
        }
    }
}
