# mpGESTÃO — Mini Sistema de Gestão de Produtos

**Murilo Machado dos Santos Pellegrini — RA 60006899**

Aplicação acadêmica em PHP orientado a objetos, MySQL/PDO, HTML, CSS, Bootstrap 5.3.3 e JavaScript puro. Identidade visual inspirada em [mpSOFTWARE](https://mpellegrini.software): fundo escuro, verde e assinatura `>mp_`.

## Funcionalidades

- Cadastro e autenticação de usuários por sessão.
- Cadastro de fornecedores, produtos e cestas no banco de dados.
- Área independente de edição dos três cadastros via `fetch`/AJAX.
- Catálogo com checkboxes e validação de seleção no cliente e no servidor.
- Cestas persistentes por usuário, uma unidade de cada produto, total monetário e número de produtos.
- Remoção de itens da cesta; isolamento dos registros por proprietário.
- Criação automática do banco e tabelas por instalador CLI idempotente.

## Abrir no Windows

Extraia o ZIP inteiro e dê dois cliques em **Iniciar.cmd**. O iniciador procura PHP no PATH ou XAMPP em `C:\xampp` / `D:\xampp`. Quando necessário, inicia o MySQL do XAMPP, prepara as tabelas, abre o servidor e o navegador em **http://127.0.0.1:8000**. Sem PHP, tenta o Docker Compose, caso o Docker Desktop esteja instalado e aberto.

O iniciador não instala dependências nem altera a política de execução do Windows. PHP 8.2+ com PDO MySQL e um servidor MySQL são necessários; Docker Compose é a alternativa. Para banco personalizado, configure `config/local.php`. Feche a janela do servidor PHP para encerrar; com Docker, use `docker compose down`.

Para apenas conferir o visual sem instalar nada, abra **PREVIA.html**. É uma captura HTML da aplicação, com dados fictícios e controles desativados; cadastros e login funcionam somente no servidor.

## Executar manualmente no Windows / VS Code

Requisitos: PHP 8.2+ com PDO MySQL, MySQL 8.0.16+ (ou MariaDB 10.11+) e Git. Bootstrap está incluído localmente; o site não depende de CDN.

1. Abra a pasta do projeto no VS Code.
2. Inicie o MySQL. Em XAMPP, ative o serviço MySQL e use `C:\xampp\php\php.exe` se `php` não estiver no PATH.
3. Copie `config/local.example.php` para `config/local.php` e ajuste host, porta, usuário e senha do banco.
4. No terminal da pasta do projeto:

```powershell
php bin/install.php
php -S 127.0.0.1:8000 -t public
```

5. Abra **http://127.0.0.1:8000**, escolha **Cadastre-se** e crie sua conta.
6. Em **Cadastros**, crie primeiro um fornecedor; depois um produto vinculado e uma cesta.
7. Em **Catálogo**, selecione os produtos e a cesta de destino. Confira o total em **Minhas cestas**.
8. Em **Editar registros**, altere fornecedor, produto ou cesta e salve sem recarregar a página.

O usuário do instalador precisa de permissão para criar banco/tabelas. Não publique `config/local.php`. O servidor embutido do PHP destina-se ao desenvolvimento; a raiz HTTP deve ser somente `public/`.

## Executar com Docker Compose

```sh
docker compose up --build -d
```

Acesse http://127.0.0.1:8000. O serviço aguarda a saúde do MySQL e executa o instalador automaticamente. As credenciais no Compose são exclusivamente para desenvolvimento local. O volume `mysql_data` preserva os dados.

## Estrutura e orientação a objetos

```text
app/          Entidades, autenticação, validação, banco e repositório PDO
bin/          Instalador do banco
config/       Configuração por ambiente ou arquivo local ignorado pelo Git
database/     Modelo SQL com chaves e restrições
docs/         Planejamento, DER e esboços
public/       Única pasta exposta pelo servidor HTTP
tests/        Testes de domínio e integração com banco
```

`Usuario` possui cestas. `Produto` referencia um objeto `Fornecedor`. `Cesta` mantém objetos `Produto`, elimina duplicatas e calcula quantidade e total em centavos inteiros. `Repository` hidrata essas relações a partir do PDO e restringe operações ao usuário autenticado.

## Modelo de dados

![DER completo](docs/der.svg)

O arquivo [schema.sql](database/schema.sql) contém todos os campos, índices, chaves estrangeiras e restrições. A chave primária `(cesta_id, produto_id)` impede duplicação de produtos. As chaves estrangeiras compostas com `usuario_id` impedem relações entre registros de proprietários diferentes. Importável no MySQL Workbench via **File → Import → Reverse Engineer MySQL Create Script**.

## Esboços das telas

![Esboços exportados do Figma](docs/figma-esbocos.png)

[Abrir arquivo editável no Figma](https://www.figma.com/design/mtPqZOrZAIF2Trn25V201X)

As sete telas cobrem login, cadastro de conta, visão geral, cadastros, edição AJAX, catálogo e resumo da cesta. A imagem acima foi exportada do Figma e conferida visualmente. Os esboços registram o estudo inicial no Figma. A interface implementada recebeu depois um refinamento: tipografia do sistema, contraste mais discreto, títulos diretos e resumo de produtos em tabela. São representações dos fluxos, não capturas da aplicação. O estudo inicial permanece em [esbocos.svg](docs/esbocos.svg).

## Autenticação e decisões

O enunciado usa “SHA254”, interpretado como **SHA-256**. Armazenamento: `hash('sha256', salt + senha)`, com salt aleatório individual de 16 bytes representado em hexadecimal. O hash é comparado com `hash_equals`. Em aplicações reais, recomenda-se um algoritmo específico para senhas, como Argon2id, em vez de SHA-256 simples.

Há token CSRF nas alterações, consultas preparadas, escape de HTML, regeneração da sessão após login e cookies HttpOnly/SameSite. O escopo é acadêmico; não há estoque, pagamento nem fechamento de pedido. O total usa o preço atual do cadastro, e cada item corresponde a uma unidade. A aplicação não implementa exclusão dos cadastros principais; apenas remoção de produtos de uma cesta.

## Verificação

```sh
php tests/domain.php
# Use um banco exclusivo cujo nome termine em _test:
DB_NAME=mpgestao_test php tests/integration.php
```

No PowerShell, use `$env:DB_NAME="mpgestao_test"` antes do segundo comando. Se existir `config/local.php`, esse arquivo prevalece sobre as variáveis; ajuste-o para o banco de testes. Consulte [TESTES.md](docs/TESTES.md) para os resultados efetivamente executados.

## Entrega Git

Os commits locais preservam as etapas de construção. O repositório está em https://github.com/Pellegrini196/mpgestao. Conceda ao professor acesso pelo GitHub para permitir a avaliação. Comentário da entrega: **Murilo Machado dos Santos Pellegrini — RA 60006899**.

## Referências e dependências

- [PHP PDO](https://www.php.net/manual/pt_BR/book.pdo.php)
- [Bootstrap 5.3](https://getbootstrap.com/docs/5.3/getting-started/introduction/) — licença MIT, preservada no cabeçalho do CSS.
- [Markdown Guide](https://www.markdownguide.org/)
- [Planejamento](docs/PLANEJAMENTO.md)
