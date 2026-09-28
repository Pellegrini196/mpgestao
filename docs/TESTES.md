# Verificações — 25/09/2026

Executadas em PHP 8.3.6 e MariaDB 10.11.14, com PDO MySQL, em banco isolado de testes:

- Sintaxe dos 16 arquivos PHP: aprovada.
- JavaScript (`node --check`): aprovado.
- Domínio (`tests/domain.php`): 7 verificações aprovadas.
- Integração (`tests/integration.php`): 12 verificações aprovadas, incluindo hash/salt, e-mail duplicado, seleção vazia, duplicação de itens, persistência, atualização de preço, autorização por proprietário e remoção.
- Fluxo HTTP (`tests/http_smoke.py`): cadastro, login, criação dos três registros, inclusão na cesta, resumo, edição AJAX dos três elementos, CSRF e logout aprovados.
- DER e esboços SVG renderizados e inspecionados. Em 26/09/2026, sete esboços foram criados no Figma, conferidos visualmente e exportados para o README.

A instalação idempotente foi executada duas vezes. O servidor PHP de teste usou um diretório de sessões gravável, pois o ambiente temporário não fornece a pasta padrão do pacote Debian.

Não foram executados: Docker Compose, MySQL 8.4 (os testes usaram MariaDB), inspeção visual da aplicação completa em navegador e execução no Windows. O teste HTTP deixa os próprios registros fictícios no banco exclusivo de teste. Não use dados reais nesse banco.

Para repetir o smoke HTTP, inicie o servidor apontando para um banco terminado em `_test` e execute:

```sh
python tests/http_smoke.py http://127.0.0.1:8000
```

## Revisão da interface — 26/09/2026

Após o refinamento visual, a sintaxe PHP e JavaScript, as sete verificações de domínio e o fluxo HTTP completo foram executados novamente e aprovados. O servidor PHP foi iniciado no ambiente de desenvolvimento e respondeu em localhost. As páginas foram capturadas como HTML a partir das respostas reais do PHP com dados fictícios.

O navegador remoto não permite acesso a esse localhost ou a arquivos locais; portanto, a revisão visual em navegador desta nova versão ficou pendente. `Iniciar.cmd` foi preparado para Windows, mas não executado em um Windows real neste ambiente. O banco de demonstração não acompanha o projeto.

## Temas — 28/09/2026

- `node --check public/assets/theme.js` e `node --check public/assets/app.js`: aprovados.
- `node tests/theme.cjs`: aprovados os cenários de seleção de tema, preferência do sistema, restauração da escolha, sincronização entre páginas, estado `aria-pressed` e armazenamento indisponível.
- Contraste calculado para texto, texto secundário, links e botões dos dois temas: acima de 4,5:1 nos pares conferidos.
- Fonte Inter variável incluída localmente, com licença SIL OFL; eixos de peso e tamanho óptico conferidos no arquivo.
- Prévia HTML atualizada com os mesmos estilos, fonte e seletor de tema da aplicação.

A inspeção visual e a interação em navegador da aplicação continuam pendentes neste ambiente. Os testes de tema executam o JavaScript em um ambiente DOM simulado; não substituem essa inspeção.
