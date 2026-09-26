<?php
declare(strict_types=1);
if (PHP_VERSION_ID < 80200 || !extension_loaded('pdo_mysql')) {
    fwrite(STDERR, "E necessario PHP 8.2 ou superior com PDO MySQL habilitado.\n");
    exit(1);
}
