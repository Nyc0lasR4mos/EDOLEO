# ÉdoLeo

Brechó online de alfaiataria e grifes, feito com PHP, MySQL/MariaDB, PDO, HTML, CSS, JavaScript e Bootstrap 5.3.

## Executar pelo VS Code

Este projeto contém PHP, então a extensão Live Server (botão **Go Live**) não interpreta as páginas `.php`. Para iniciar a loja dentro do VS Code sem NPM, use a tarefa já configurada:

1. Abra o painel do XAMPP e inicie **MySQL**.
2. Copie `.env.example` para `.env`.
3. Importe `database/schema.sql` pelo phpMyAdmin (`http://localhost/phpmyadmin`).
4. No VS Code, pressione `Ctrl+Shift+B` e escolha **Go Live (ÉdoLeo via PHP)**. A tarefa abre o navegador no endereço correto (`127.0.0.1:8000`).

O terminal do VS Code mantém o servidor ativo. Para encerrá-lo, clique no terminal e pressione `Ctrl+C`. Se a instalação do XAMPP estiver em outro local, ajuste o caminho do PHP em `.vscode/tasks.json`.

## Executar no Apache do XAMPP

1. Copie `.env.example` para `.env` e configure as credenciais do MySQL.
2. Importe `database/schema.sql` pelo phpMyAdmin ou pelo cliente MySQL. O script cria o banco, as tabelas, as categorias e o catálogo de demonstração.
3. Coloque a pasta dentro de `xampp/htdocs/edoleo` e abra `http://localhost/edoleo/`.
4. Crie uma conta pela página **Entrar**.
5. Para habilitar o painel administrativo, promova a conta criada no banco: `UPDATE users SET role='admin' WHERE email='seu@email.com';`. Acesse `http://localhost/edoleo/admin/`.

O checkout simula a confirmação de pagamento (PIX, boleto ou cartão); ele não se conecta a um provedor financeiro. As fotografias de demonstração carregam do Unsplash.

## Preparar para o GitHub

- O arquivo `.env` contém configurações locais e está ignorado pelo Git. Nunca publique senhas ou credenciais nele.
- Mantenha `.env.example` no repositório como modelo e configure valores próprios no ambiente de hospedagem.
- O banco de demonstração é carregado por `database/schema.sql`; importe-o uma vez em um banco MySQL/MariaDB vazio.
- O painel só abre para contas com `role='admin'`; promova uma conta pelo SQL indicado acima após cadastrar-se.
- Este projeto usa PHP e MySQL/MariaDB e não precisa de `npm install` ou `npm run dev`.

## Recursos

- Catálogo do banco com filtro por categoria e busca.
- Página individual com descrição, preço e disponibilidade.
- Carrinho em sessão, atualização de quantidades e limite pelo estoque.
- Cadastro e autenticação usando `password_hash()` e `password_verify()`.
- Checkout transacional com bloqueio de estoque, registro do pedido e dos itens, baixa de estoque e limpeza da sacola.
- Painel `/admin/` com sessão e perfil administrativo, CRUD de catálogo via cadastro/edição/ativação e consulta de pedidos detalhados.
- Credenciais por `.env`, modelo em `.env.example`; queries parametrizadas com PDO e formulários protegidos por token CSRF.
