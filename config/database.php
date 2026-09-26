<?php
declare(strict_types=1);
$local = is_file(__DIR__.'/local.php') ? require __DIR__.'/local.php' : [];
return array_replace([
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'name' => getenv('DB_NAME') ?: 'mini_produtos',
    'user' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
], $local);
