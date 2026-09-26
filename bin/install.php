<?php
require __DIR__.'/../app/bootstrap.php';
try { App\Database::install(); echo "Banco e tabelas criados/verificados com sucesso.\n"; } catch (Throwable $e) { fwrite(STDERR, "Falha na instalação: ".$e->getMessage()."\n"); exit(1); }
