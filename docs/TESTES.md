# Verificações — 25/09/2026

Executadas em PHP 8.3.6 e MariaDB 10.11.14, com PDO MySQL, em banco isolado de testes:

- Sintaxe dos 16 arquivos PHP: aprovada.
- JavaScript (`node --check`): aprovado.
- Domínio (`tests/domain.php`): 7 verificações aprovadas.
- Integração (`tests/integration.php`): 12 verificações aprovadas, incluindo hash/salt, e-mail duplicado, seleção vazia, duplicação de itens, persistência, atualização de preço, autorização por proprietário e remoção.
- Fluxo HTTP (`tests/http_smoke.py`): cadastro, login, criação dos três registros, inclusão na cesta, resumo, edição AJAX dos três elementos, CSRF e logout aprovados.
- DER e esboços SVG renderizados e inspecionados. Em 26/09/2026, sete esboços foram criados no Figma, conferidos visualmente e exportados para o README.

A instalação idempotente foi executada duas vezes. O servidor PHP de teste usou um diretório de sessões gravável, pois o ambiente temporário não fornece a pasta padrão do pacote Debian.

Não foram executados: Docker Compose, MySQL 8.4 (os testes usaram MariaDB), inspeção visual da aplicação completa em navegador e execução no Windows do aluno. O teste HTTP deixa os próprios registros fictícios no banco exclusivo de teste. Não use dados reais nesse banco.

Para repetir o smoke HTTP, inicie o servidor apontando para um banco terminado em `_test` e execute:

```sh
python tests/http_smoke.py http://127.0.0.1:8000
```
