# DoceFlow — hospedagem (Opção A)

## Local (XAMPP)

O app fica **no mesmo host do painel**:

- Painel: `http://localhost/pjt/xd360/painel`
- DoceFlow: `http://localhost/pjt/xd360/doceflow/` (ponte em `xd360/doceflow/index.php`)

No `.env` do xd360, deixe `DOCEFLOW_PUBLIC_URL` **vazio** para usar esse caminho.

Requisitos: `mod_rewrite` ativo; pasta `pjt/doceflow` instalada com `composer install` e banco `doceflow` criado.

## Produção (visão)

Subdomínio do tenant: `https://{slug}.xd360.com.br/doceflow/` → alias Apache para `doceflow/public` (ou mesma ponte sob o vhost do tenant).

Domínio customizado do cliente: **fora do escopo atual** — retomar só se necessário no roadmap comercial.
