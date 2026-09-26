# Planejamento de execução

1. **Análise:** identificar cadastro/login, cadastros de fornecedor, produto e cesta, edição AJAX, catálogo com checkboxes e resumo da cesta. Critério: navegação clara, validação e estados vazios.
2. **Modelagem:** usuários 1:N fornecedores, produtos e cestas; fornecedor 1:N produtos; cesta N:M produtos por cesta_produtos. Critério: DER com todos os campos e chaves.
3. **Backend:** classes de domínio, repositórios PDO, autenticação por sessão, autorização por proprietário, CSRF e instalação idempotente.
4. **Frontend:** HTML, Bootstrap local, CSS e JavaScript puro. Cadastro tradicional; edição via fetch/AJAX; catálogo com seleção múltipla; cesta com total e contagem.
5. **Validação:** testes de integração HTTP/MySQL, duplicidade, autenticação, autorização, CSRF, valores monetários e persistência. Documentar resultados efetivamente obtidos.
6. **Entrega:** README, imagens dos esboços, DER, SQL, instruções Windows/VS Code/Docker e histórico Git. Nome e RA preenchidos; publicar em novo repositório privado autorizado pelo aluno.

## Decisões
- “SHA254” interpretado como SHA-256. Confirmar com o professor.
- Uma unidade por produto em cada cesta, garantida por chave primária composta.
- Dados isolados por usuário; não existe cadastro público de produtos.
- Valor na cesta é o preço atual do produto; edição de preço recalcula os resumos.
- A aplicação não oferece exclusão de fornecedores/produtos; as chaves estrangeiras também preservam seus relacionamentos.
- Não há pagamento, estoque ou finalização de pedido: fora do escopo.
- Esboços são documentação de planejamento, não capturas de funcionalidades testadas.
